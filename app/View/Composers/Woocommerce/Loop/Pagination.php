<?php

namespace App\View\Composers\WooCommerce\Loop;

use Roots\Acorn\View\Composer;

class Pagination extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'woocommerce.loop.pagination',
    ];

    public function with(): array
    {
        return [
            'links' => $this->paginationLinks(),
        ];
    }

    /**
     * Retrieve the pagination links.
     */
    protected function paginationLinks(array $opts = []): array
    {
        if (!function_exists('wc_get_loop_prop')) {
            return [];
        }

        $total = $opts['total'] ?? (int) wc_get_loop_prop('total_pages');
        $current = $opts['current'] ?? (int) wc_get_loop_prop('current_page');

        if ($total <= 1) {
            return [];
        }

        $base = $opts['base'] ?? esc_url_raw(str_replace(999999999, '%#%', remove_query_arg('add-to-cart', get_pagenum_link(999999999, false))));
        $format = $opts['format'] ?? '';

        $links = paginate_links([
            'base' => $base,
            'format' => $format,
            'add_args' => false,
            'current' => max(1, $current),
            'total' => $total,
            'prev_text' => __('Previous', 'woocommerce'),
            'next_text' => __('Next', 'woocommerce'),
            'type' => 'array',
            'end_size' => 3,
            'mid_size' => 3,
        ]);

        return is_array($links) ? $links : [];
    }
}
