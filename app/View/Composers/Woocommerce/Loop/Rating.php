<?php

namespace App\View\Composers\WooCommerce\Loop;

use Roots\Acorn\View\Composer;

class Rating extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'woocommerce.loop.rating',
    ];

    public function with(): array
    {
        $product = $this->resolveProduct();

        if (!$product || !function_exists('wc_review_ratings_enabled') || !wc_review_ratings_enabled()) {
            return [
                'rating_enabled' => false,
                'rating_avg' => 0,
                'rating_count' => 0,
                'rating_avg_display' => 0,
                'rating_label' => '',
                'rating_fills' => [],
            ];
        }

        $avg = (float) $product->get_average_rating();
        $count = (int) $product->get_rating_count();

        if ($avg <= 0) {
            return [
                'rating_enabled' => false,
                'rating_avg' => 0,
                'rating_count' => $count,
                'rating_avg_display' => 0,
                'rating_label' => '',
                'rating_fills' => [],
            ];
        }

        $avgDisplay = floor($avg) == $avg ? (int) $avg : number_format($avg, 1);

        return [
            'rating_enabled' => true,
            'rating_avg' => $avg,
            'rating_count' => $count,
            'rating_avg_display' => $avgDisplay,
            'rating_label' => sprintf(
                esc_html__('Rated %s out of 5', 'woocommerce'),
                $avgDisplay
            ),
            'rating_fills' => $this->fills($avg), // [100, 100, 60, 0, 0] etc.
        ];
    }

    /**
     * Resolve the WC product from:
     * 1) a $product passed into the view,
     * 2) the global $product,
     * 3) current post ID.
     */
    protected function resolveProduct()
    {
        // 1) View data (passed explicitly)
        $passed = $this->data->get('product');
        if ($passed && is_object($passed) && method_exists($passed, 'get_average_rating')) {
            return $passed;
        }

        // 2) Global
        if (!empty($GLOBALS['product']) && is_object($GLOBALS['product'])) {
            return $GLOBALS['product'];
        }

        // 3) Fallback by post ID
        if (function_exists('wc_get_product')) {
            $maybe = wc_get_product(get_the_ID());
            if ($maybe) {
                return $maybe;
            }
        }

        return null;
    }

    /**
     * Build 5 star fill percentages (0–100).
     */
    protected function fills(float $avg): array
    {
        $fills = [];
        for ($i = 1; $i <= 5; $i++) {
            $fills[] = min(max($avg - ($i - 1), 0), 1) * 100;
        }
        return $fills;
    }
}
