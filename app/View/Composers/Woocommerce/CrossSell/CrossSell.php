<?php

namespace App\View\Composers\WooCommerce\CrossSell;

use Roots\Acorn\View\Composer;

class CrossSell extends Composer
{
    protected static $views = [
        'woocommerce.cross-sell.cross-sell',
    ];

    public function with(): array
    {
        // Build a quick snapshot of the cart to reuse in both sections
        [$inCartProductIds, $inCartTopLevelCatIds] = $this->cartSnapshot();

        return [
            'checkout_url'      => $this->checkoutUrl(),
            'crossSellProducts' => $this->cartCrossSellProducts($inCartProductIds, 24),
            'categoryLoops'     => $this->enabledCategoryLoops($inCartProductIds, $inCartTopLevelCatIds, 8),
        ];
    }

    /** Nonce-guarded checkout URL so checkout is reachable after the cross-sell page */
    private function checkoutUrl(): string
    {
        if (!function_exists('wc_get_checkout_url')) {
            return esc_url(home_url('/checkout'));
        }
        $token = wp_create_nonce('wc_cross_sell_pass');

        return esc_url(add_query_arg(
            [
                'passed_cross_sell' => '1',
                'cs_token'          => $token,
            ],
            wc_get_checkout_url()
        ));
    }

    /**
     * Take one snapshot of the cart:
     *  - list of product IDs already in cart (includes variation + parent IDs)
     *  - list of top-level category IDs represented in the cart
     *
     * @return array{0:int[],1:int[]}
     */
    private function cartSnapshot(): array
    {
        $inCartProductIds   = [];
        $inCartTopLevelCats = [];

        if (!function_exists('WC') || !WC()->cart) {
            return [[], []];
        }

        foreach ((array) WC()->cart->get_cart() as $item) {
            if (empty($item['data']) || !($item['data'] instanceof \WC_Product)) {
                continue;
            }

            /** @var \WC_Product $p */
            $p = $item['data'];
            $pid = (int) $p->get_id();
            $inCartProductIds[] = $pid;

            $parentId = $p->is_type('variation') ? (int) $p->get_parent_id() : 0;
            if ($parentId) {
                $inCartProductIds[] = $parentId;
            }

            // Figure out top-level categories for this product (use parent for variations)
            $termSourceId = $parentId ?: $pid;
            $terms = get_the_terms($termSourceId, 'product_cat');

            if (!empty($terms) && !is_wp_error($terms)) {
                foreach ($terms as $t) {
                    $top = $this->topLevelTerm($t);
                    if ($top) {
                        $inCartTopLevelCats[] = (int) $top->term_id;
                    }
                }
            }
        }

        $inCartProductIds   = array_values(array_unique(array_map('intval', $inCartProductIds)));
        $inCartTopLevelCats = array_values(array_unique(array_map('intval', $inCartTopLevelCats)));

        return [$inCartProductIds, $inCartTopLevelCats];
    }

    /** Return the top-level ancestor for a category term */
    private function topLevelTerm(\WP_Term $term): ?\WP_Term
    {
        if ($term->taxonomy !== 'product_cat') return null;

        $top = $term;
        while (!empty($top->parent)) {
            $parent = get_term((int) $top->parent, 'product_cat');
            if (!$parent || is_wp_error($parent)) break;
            $top = $parent;
        }
        return $top;
    }

    /**
     * Gather unique cross-sell products from ALL items in the cart.
     * - Deduped
     * - Visible only
     * - Excludes products already in the cart (by ID and by parent ID)
     */
    private function cartCrossSellProducts(array $inCartProductIds, int $limit = 24): array
    {
        if (!function_exists('WC') || !WC()->cart) {
            return [];
        }

        $cart = WC()->cart->get_cart();
        if (empty($cart)) {
            return [];
        }

        // Collect cross-sell IDs from every line item (variation first then parent)
        $xsIds = [];
        foreach ($cart as $item) {
            if (empty($item['data']) || !($item['data'] instanceof \WC_Product)) {
                continue;
            }
            /** @var \WC_Product $p */
            $p = $item['data'];

            $ids = $p->get_cross_sell_ids();

            // fallback to parent if none
            if (empty($ids) && $p->is_type('variation')) {
                $parent = wc_get_product($p->get_parent_id());
                if ($parent) {
                    $ids = $parent->get_cross_sell_ids();
                }
            }

            foreach ((array) $ids as $id) {
                $id = (int) $id;
                if (in_array($id, $inCartProductIds, true)) {
                    continue; // exclude products already in the cart
                }
                $xsIds[$id] = true;
            }
        }

        if (empty($xsIds)) {
            return [];
        }

        // Load products, filter by visibility/in-stock/purchasable for simple
        $out = [];
        foreach (array_keys($xsIds) as $id) {
            $p = wc_get_product((int) $id);
            if (!$p instanceof \WC_Product) continue;
            if (!$p->is_visible()) continue;

            // For simple products, prefer purchasable & in-stock
            if ($p->is_type('simple') && (!$p->is_purchasable() || !$p->is_in_stock())) {
                continue;
            }

            // Extra guard (if a variation added via cross-sell, also skip if its parent is in cart)
            $parentId = $p->is_type('variation') ? (int) $p->get_parent_id() : 0;
            if ($parentId && in_array($parentId, $inCartProductIds, true)) {
                continue;
            }

            $out[] = $p;
            if (count($out) >= $limit) break;
        }

        return $out;
    }

    /**
     * Build carousels for ALL enabled top-level categories, with exclusions:
     * - Exclude categories that are already in the cart (top-level)
     * - Exclude products already in the cart (and their parents)
     * - Respects your parent-category toggle:
     *     - wc_cs_category_row_enabled( $top_term_id ) === true
     *     - OR pcs_is_top_parent_enabled( $top_term_id ) === true
     *     - Default: enabled unless meta 'wc_cs_show_row' == 'no'
     */
    private function enabledCategoryLoops(array $inCartProductIds, array $inCartTopLevelCatIds, int $limit = 8): array
    {
        $terms = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'parent'     => 0,
        ]);

        if (is_wp_error($terms) || empty($terms)) {
            return [];
        }

        $blocks = [];

        foreach ($terms as $term) {
            $topId = (int) $term->term_id;

            // Skip categories already represented in the cart
            if (in_array($topId, $inCartTopLevelCatIds, true)) {
                continue;
            }

            // Respect admin toggle on TOP-LEVEL term
            $enabled = true;
            if (function_exists('pcs_is_top_parent_enabled')) {
                $enabled = pcs_is_top_parent_enabled($topId);
            } elseif (function_exists('wc_cs_category_row_enabled')) {
                $enabled = wc_cs_category_row_enabled($topId);
            } else {
                // direct meta fallback (default yes)
                $val = get_term_meta($topId, 'wc_cs_show_row', true);
                $enabled = ($val !== 'no');
            }
            if (!$enabled) continue;

            // Fetch up to $limit best-sellers (Woo "popularity" respects total_sales)
            $products = wc_get_products([
                'status'     => 'publish',
                'limit'      => $limit * 2, // pull extra, we'll filter out overlaps/in-cart
                'orderby'    => 'popularity',
                'order'      => 'DESC',
                'return'     => 'objects',
                'paginate'   => false,
                'category'   => [$term->slug], // includes children
                'visibility' => 'visible',
            ]);

            if (empty($products)) continue;

            // Filter for visible and not already in the cart (including parent matches)
            $filtered = [];
            foreach ($products as $p) {
                if (!$p instanceof \WC_Product) continue;
                if (!$p->is_visible()) continue;

                $pid      = (int) $p->get_id();
                $parentId = $p->is_type('variation') ? (int) $p->get_parent_id() : 0;

                if (in_array($pid, $inCartProductIds, true)) continue;
                if ($parentId && in_array($parentId, $inCartProductIds, true)) continue;

                $filtered[] = $p;
                if (count($filtered) >= $limit) break;
            }

            if (!$filtered) continue;

            $blocks[] = [
                'term'     => $term,
                'title'    => $term->name,
                'url'      => esc_url(get_term_link($term)),
                'products' => $filtered,
            ];
        }

        return $blocks;
    }
}