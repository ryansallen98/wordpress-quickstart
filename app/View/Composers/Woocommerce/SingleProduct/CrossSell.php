<?php

namespace App\View\Composers\WooCommerce\SingleProduct;

use Roots\Acorn\View\Composer;

class CrossSell extends Composer
{
    protected static $views = [
        'woocommerce.single-product.cross-sell',
    ];

    public function with(): array
    {
        /** @var \WC_Product|\WC_Product_Variation|null $product */
        $product = $this->data['product'] ?? null;
        $crossSells = $this->data['cross_sells'] ?? [];

        return [
            'purchasedImageHtml' => $this->purchasedImageHtml($product),
            'continueUrl' => $this->continueUrl(),
            'cartUrl' => $this->cartUrl(),
            'checkoutUrl' => $this->checkoutBypassUrl(),

            'crossSellProducts' => array_values(array_filter($crossSells, static function ($p) {
                return $p instanceof \WC_Product && $p->is_visible();
            })),

            'relatedProducts' => $this->relatedProducts($product, $crossSells, 8),

            // IMPORTANT: this now skips rows for any disabled top-level category
            'categoryLoops' => $this->categoryBestSellerBlocksExcludingProductCats($product),
        ];
    }

    protected function purchasedImageHtml($product): string
    {
        if (!$product instanceof \WC_Product) {
            return '';
        }

        // If we have a variation, prefer its image, else parent image.
        if ($product instanceof \WC_Product_Variation) {
            $imgId = (int) $product->get_image_id();
            if (!$imgId) {
                $parent = wc_get_product($product->get_parent_id());
                if ($parent instanceof \WC_Product) {
                    $imgId = (int) $parent->get_image_id();
                }
            }
            if ($imgId) {
                return wp_get_attachment_image(
                    $imgId,
                    'woocommerce_thumbnail',
                    false,
                    ['class' => 'rounded-md w-full h-full object-cover']
                );
            }
            // Last-ditch: render parent product image HTML if any
            $parent = wc_get_product($product->get_parent_id());
            return $parent instanceof \WC_Product
                ? $parent->get_image('woocommerce_thumbnail', ['class' => 'rounded-md w-full h-full object-cover'])
                : '';
        }

        // Non-variation: normal product image, fallback empty string
        return $product->get_image('woocommerce_thumbnail', [
            'class' => 'rounded-md w-full h-full object-cover',
        ]) ?: '';
    }

    protected function continueUrl(): string
    {
        // Always send users to the Shop page (fallback to site root if no shop page)
        if (function_exists('wc_get_page_permalink')) {
            $shop = wc_get_page_permalink('shop');
            if (!empty($shop)) {
                return esc_url($shop);
            }
        }
        return esc_url(home_url('/'));
    }

    protected function cartUrl(): string
    {
        return esc_url(function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart'));
    }

    protected function checkoutBypassUrl(): string
    {
        $url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/checkout');
        $nonce = wp_create_nonce('wc_cross_sell_pass');
        return esc_url(add_query_arg(['passed_cross_sell' => '1', 'cs_token' => $nonce], $url));
    }

    protected function relatedProducts($product, array $crossSells, int $limit = 8): array
    {
        if (!$product instanceof \WC_Product)
            return [];
        $exclude = [$product->get_id()];
        foreach ($crossSells as $p)
            if ($p instanceof \WC_Product)
                $exclude[] = $p->get_id();
        $exclude = array_values(array_unique(array_map('intval', $exclude)));
        $related_ids = wc_get_related_products($product->get_id(), $limit + 10, $exclude);
        if (empty($related_ids))
            return [];
        $out = [];
        foreach ($related_ids as $pid) {
            if (count($out) >= $limit)
                break;
            $p = wc_get_product($pid);
            if ($p instanceof \WC_Product && $p->is_visible())
                $out[] = $p;
        }
        return $out;
    }

    /**
     * Build best-seller blocks for top-level categories,
     * excluding:
     *  - any top-level category the product belongs to
     *  - any top-level category DISABLED via the admin toggle
     */
    protected function categoryBestSellerBlocksExcludingProductCats($product): array
    {
        // Product’s own top-level categories (to exclude)
        $pid = ($product instanceof \WC_Product_Variation) ? $product->get_parent_id()
            : (($product instanceof \WC_Product) ? $product->get_id() : 0);

        $product_top_level_ids = [];
        if ($pid) {
            $terms = get_the_terms($pid, 'product_cat');
            if (!empty($terms) && !is_wp_error($terms)) {
                foreach ($terms as $t) {
                    // climb to top level
                    $top = $t;
                    while (!empty($top->parent)) {
                        $parent = get_term((int) $top->parent, 'product_cat');
                        if (!$parent || is_wp_error($parent))
                            break;
                        $top = $parent;
                    }
                    $product_top_level_ids[(int) $top->term_id] = true;
                }
            }
        }

        // Top-level cats
        $terms = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'parent' => 0,
        ]);
        if (is_wp_error($terms) || empty($terms))
            return [];

        $blocks = [];
        foreach ($terms as $term) {
            // Skip if product belongs to this top-level cat
            if (isset($product_top_level_ids[(int) $term->term_id]))
                continue;

            // Skip if this top-level cat is disabled via toggle
            if (function_exists('pcs_is_top_parent_enabled')) {
                if (!pcs_is_top_parent_enabled((int) $term->term_id))
                    continue;
            } elseif (function_exists('wc_cs_category_row_enabled')) {
                if (!wc_cs_category_row_enabled((int) $term->term_id))
                    continue;
            } else {
                // Fallback: direct meta check (default enabled)
                $val = get_term_meta((int) $term->term_id, 'wc_cs_show_row', true);
                if ($val === 'no')
                    continue;
            }

            // Fetch up to 8 best-sellers in this category
            $products = wc_get_products([
                'status' => 'publish',
                'limit' => 8,
                'orderby' => 'popularity',
                'order' => 'DESC',
                'return' => 'objects',
                'paginate' => false,
                'category' => [$term->slug], // Woo includes children
                'visibility' => 'visible',
            ]);
            if (empty($products))
                continue;

            $products = array_values(array_filter(
                $products,
                static fn($p) =>
                $p instanceof \WC_Product && $p->is_visible()
            ));
            if (!$products)
                continue;

            $blocks[] = [
                'term' => $term,
                'title' => $term->name,
                'url' => esc_url(get_term_link($term)),
                'products' => $products,
            ];
        }

        return $blocks;
    }
}