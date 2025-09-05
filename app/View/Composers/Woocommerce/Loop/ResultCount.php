<?php

namespace App\View\Composers\WooCommerce\Loop;

use Roots\Acorn\View\Composer;

class ResultCount extends Composer
{
    protected static $views = [
        'woocommerce.loop.result-count',
    ];

    public function with(): array
    {
        // Pull loop props (Woo sets these)
        $total = (int) (function_exists('wc_get_loop_prop') ? wc_get_loop_prop('total') : 0);
        $perPage = (int) (function_exists('wc_get_loop_prop') ? wc_get_loop_prop('per_page') : 0);
        $current = (int) (function_exists('wc_get_loop_prop') ? wc_get_loop_prop('current_page') : 1);

        // Resolve a human label for "orderby" (what the user sorted by)
        $orderedByLabel = $this->orderedByLabel();

        // Nothing to show
        if ($total <= 0) {
            return [
                'wc_result_total' => 0,
                'wc_result_is_sorted' => false,
                'wc_result_count_html' => '',
            ];
        }

        // Branches mirror Woo’s template
        if ($total === 1) {
            $html = esc_html__('Showing the single result', 'woocommerce');
        } elseif ($total <= $perPage || $perPage === -1) {
            $orderedbyPlaceholder = empty($orderedByLabel) ? '%2$s' : '<span class="screen-reader-text">%2$s</span>';
            $html = sprintf(
                /* translators: 1: total results 2: sorted by */
                _n('Showing all %1$d result', 'Showing all %1$d results', $total, 'woocommerce') . $orderedbyPlaceholder,
                $total,
                esc_html($orderedByLabel)
            );
        } else {
            $first = ($perPage * $current) - $perPage + 1;
            $last = min($total, $perPage * $current);
            $orderedbyPlaceholder = empty($orderedByLabel) ? '%4$s' : '<span class="screen-reader-text">%4$s</span>';

            $html = sprintf(
                /* translators: 1: first result 2: last result 3: total results 4: sorted by */
                _nx(
                    'Showing %1$d–%2$d of %3$d result',
                    'Showing %1$d–%2$d of %3$d results',
                    $total,
                    'with first and last result',
                    'woocommerce'
                ) . $orderedbyPlaceholder,
                $first,
                $last,
                $total,
                esc_html($orderedByLabel)
            );
        }

        return [
            'wc_result_total' => $total,
            'wc_result_is_sorted' => !empty($orderedByLabel) && $total > 1,
            'wc_result_count_html' => $html,
        ];
    }

    /**
     * Return a human-readable "Sorted by" label from the current orderby query var.
     */
    protected function orderedByLabel(): string
    {
        // Current orderby (e.g. 'menu_order', 'popularity', 'rating', 'date', 'price', 'price-desc', etc.)
        $orderby = '';
        if (isset($_GET['orderby'])) {
            $orderby = wc_clean(wp_unslash($_GET['orderby']));
        } elseif (function_exists('get_option')) {
            $orderby = get_option('woocommerce_default_catalog_orderby', '');
        }

        if ($orderby === '') {
            return '';
        }

        // Build options the same way Woo does
        $catalog_orderby_options = apply_filters(
            'woocommerce_default_catalog_orderby_options',
            [
                'menu_order' => __('Default sorting', 'woocommerce'),
                'popularity' => __('Sort by popularity', 'woocommerce'),
                'rating' => __('Sort by average rating', 'woocommerce'),
                'date' => __('Sort by latest', 'woocommerce'),
                'price' => __('Sort by price: low to high', 'woocommerce'),
                'price-desc' => __('Sort by price: high to low', 'woocommerce'),
            ]
        );

        return isset($catalog_orderby_options[$orderby]) ? (string) $catalog_orderby_options[$orderby] : '';
    }
}