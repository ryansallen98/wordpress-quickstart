<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Roots\Acorn\Sage\SageServiceProvider;
use function Roots\view;

class WooCommerceProductSearchServiceProvider extends SageServiceProvider
{
    public function boot()
    {
        add_action('wp_ajax_wc_product_search', [$this, 'handleAjax']);
        add_action('wp_ajax_nopriv_wc_product_search', [$this, 'handleAjax']);
    }

    public function handleAjax()
    {
        // Security
        $nonce = isset($_REQUEST['_ajax_nonce']) ? $_REQUEST['_ajax_nonce'] : '';
        if (!wp_verify_nonce($nonce, 'wcps')) {
            status_header(403);
            header('Content-Type: text/plain; charset=' . get_bloginfo('charset'));
            echo esc_html__('Invalid request.', 'wordpress-quickstart');
            wp_die();
        }

        // Query
        $q = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
        $groups = [];
        $products_total = 0;

        // Helper for highlighting matches safely
        $hi = static function (string $text, string $needle): string {
            $safe = esc_html($text);
            if ($needle === '') {
                return $safe;
            }
            return preg_replace('/' . preg_quote($needle, '/') . '/i', '<strong>$0</strong>', $safe);
        };

        // Normalize early
        $q_trim = trim((string) $q);

        // Server-side guard: ignore queries shorter than 3 chars
        if (mb_strlen($q_trim) < 3) {
            header('Content-Type: text/html; charset=' . get_bloginfo('charset'));
            echo ''; // empty fragment keeps popover hidden
            wp_die();
        }

        $q_slug = sanitize_title($q_trim);

        /**
         * 1) CATEGORIES (partial matches)
         */
        $cat_terms = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
            'number'     => 6,
            'search'     => $q_trim,
        ]);

        if (!is_wp_error($cat_terms) && !empty($cat_terms)) {
            $items = [];
            foreach ($cat_terms as $term) {
                $items[] = [
                    'url'   => esc_url(get_term_link($term)),
                    'label' => $hi($term->name, $q_trim),
                ];
            }
            $groups[] = [
                'key'     => 'product_cat',
                'heading' => __('Categories', 'wordpress-quickstart'),
                'items'   => $items,
            ];
        }

        /**
         * 2) CUSTOM PRODUCT TAXONOMIES (partial matches)
         * Excludes product_cat/product_tag and internal/utility taxonomies.
         */
        $tax_objects  = get_object_taxonomies('product', 'objects');
        $exclude      = ['product_type', 'product_visibility', 'product_shipping_class', 'product_cat', 'product_tag'];
        $custom_taxes = array_filter($tax_objects, function ($tax) use ($exclude) {
            return $tax->public && !in_array($tax->name, $exclude, true);
        });

        $per_tax_limit = 6;

        foreach ($custom_taxes as $tax) {
            $terms = get_terms([
                'taxonomy'   => $tax->name,
                'hide_empty' => false,
                'number'     => $per_tax_limit,
                'search'     => $q_trim,
            ]);

            if (is_wp_error($terms) || empty($terms)) {
                continue;
            }

            $items = [];
            foreach ($terms as $term) {
                $items[] = [
                    'url'   => esc_url(get_term_link($term)),
                    'label' => $hi($term->name, $q_trim),
                ];
            }

            $groups[] = [
                'key'     => $tax->name,
                'heading' => $tax->labels->name ?? ucfirst(str_replace(['_', '-'], ' ', $tax->name)),
                'items'   => $items,
            ];
        }

        /**
         * 3) TAGS — show ONLY the specific matching tag
         * Exact by name (case-insensitive) or exact by slug. If none, omit Tags group.
         */
        // $exact_tag = get_term_by('name', $q_trim, 'product_tag');
        // if (!$exact_tag || is_wp_error($exact_tag)) {
        //     $exact_tag = get_term_by('slug', $q_slug, 'product_tag');
        // }

        // if ($exact_tag && !is_wp_error($exact_tag)) {
        //     $groups[] = [
        //         'key'     => 'product_tag',
        //         'heading' => __('Tags', 'wordpress-quickstart'),
        //         'items'   => [[
        //             'url'   => esc_url(get_term_link($exact_tag)),
        //             'label' => $hi($exact_tag->name, $q_trim),
        //         ]],
        //     ];
        // }

        /**
         * 4) PRODUCTS — limited list + accurate total for CTA
         */
        $product_q = new \WP_Query([
            'post_type'      => 'product',
            'post_status'    => 'publish',
            's'              => $q_trim,
            'fields'         => 'ids',
            'posts_per_page' => 8,
            'no_found_rows'  => false,
        ]);

        $product_ids    = $product_q->posts ?: [];
        $products_total = (int) $product_q->found_posts;

        if (!empty($product_ids)) {
            $items = [];
            foreach ($product_ids as $pid) {
                $img = get_the_post_thumbnail_url($pid, 'woocommerce_thumbnail');
                if (!$img) {
                    $img = wc_placeholder_img_src(); // fallback if no thumbnail
                }

                $items[] = [
                    'url'   => esc_url(get_permalink($pid)),
                    'label' => $hi(get_the_title($pid), $q_trim),
                    'image' => esc_url($img),
                ];
            }

            $groups[] = [
                'key'      => 'products',
                'heading'  => __('Products', 'wordpress-quickstart'),
                'items'    => $items,
                'total'    => $products_total,
                'view_all' => add_query_arg(
                    ['s' => $q_trim], // search on shop archive
                    wc_get_page_permalink('shop')
                ),
            ];
        }

        header('Content-Type: text/html; charset=' . get_bloginfo('charset'));

        // Any results?
        $has_any_items = false;
        foreach ($groups as $g) {
            if (!empty($g['items'])) { $has_any_items = true; break; }
        }

        if (!$has_any_items && View::exists('woocommerce.search.no-results')) {
            echo view('woocommerce.search.no-results', [
                'items' => [],
                'query' => $q_trim,
            ])->render();
        } else {
            echo view('woocommerce.search.results', [
                'groups'         => $groups,
                'query'          => $q_trim,
                'products_total' => $products_total,
            ])->render();
        }

        wp_die();
    }
}