<?php

namespace App\View\Composers\WooCommerce\SingleProduct;

use Roots\Acorn\View\Composer;

class Rating extends Composer
{
    protected static $views = [
        // This is your Blade view name (see step 2)
        'woocommerce.single-product.rating',
    ];

    public function with(): array
    {
        $product = $this->resolveProduct();

        // Guards: product must exist and ratings must be enabled
        if (!$product || !function_exists('wc_review_ratings_enabled') || !wc_review_ratings_enabled()) {
            return $this->emptyState();
        }

        $ratingCount = (int) $product->get_rating_count();
        $reviewCount = (int) $product->get_review_count();
        $average     = (float) $product->get_average_rating();

        if ($ratingCount <= 0 || $average <= 0) {
            // No ratings yet: keep structure so the view can decide to hide
            return [
                'rating_enabled'     => true,
                'rating_count'       => 0,
                'review_count'       => $reviewCount,
                'rating_avg'         => 0.0,
                'rating_avg_display' => 0,
                'rating_label'       => '',
                'rating_fills'       => [],
                'rating_html'        => '',  // wc_get_rating_html output
                'review_link_html'   => '',  // "(N customer reviews)" link
                'comments_open'      => comments_open(get_the_ID()),
            ];
        }

        $avgDisplay = floor($average) == $average ? (int) $average : number_format($average, 1);

        // WooCommerce’s star markup
        $ratingHtml = function_exists('wc_get_rating_html')
            ? wc_get_rating_html($average, $ratingCount)
            : '';

        // Build the “(N customer review[s])” link HTML just like Woo
        $reviewLinkHtml = '';
        if (comments_open(get_the_ID())) {
            // translators: %s is the review count wrapped in a span.count
            $text = sprintf(
                _n('%s customer review', '%s customer reviews', $reviewCount, 'woocommerce'),
                '<span class="count">' . esc_html($reviewCount) . '</span>'
            );
            $reviewLinkHtml = sprintf(
                '<a href="#reviews" class="woocommerce-review-link" rel="nofollow">(%s)</a>',
                $text
            );
        }

        return [
            'rating_enabled'     => true,
            'rating_count'       => $ratingCount,
            'review_count'       => $reviewCount,
            'rating_avg'         => $average,
            'rating_avg_display' => $avgDisplay,
            'rating_label'       => sprintf(esc_html__('Rated %s out of 5', 'woocommerce'), $avgDisplay),
            'rating_fills'       => $this->fills($average), // [100, 100, 60, 0, 0]
            'rating_html'        => $ratingHtml,
            'review_link_html'   => $reviewLinkHtml,
            'comments_open'      => comments_open(get_the_ID()),
        ];
    }

    protected function resolveProduct()
    {
        // 1) explicit view data
        $passed = $this->data->get('product');
        if ($passed && is_object($passed) && method_exists($passed, 'get_average_rating')) {
            return $passed;
        }

        // 2) global WC product
        if (!empty($GLOBALS['product']) && is_object($GLOBALS['product'])) {
            return $GLOBALS['product'];
        }

        // 3) fallback by post ID
        if (function_exists('wc_get_product')) {
            $maybe = wc_get_product(get_the_ID());
            if ($maybe) {
                return $maybe;
            }
        }

        return null;
    }

    protected function fills(float $avg): array
    {
        $fills = [];
        for ($i = 1; $i <= 5; $i++) {
            $fills[] = min(max($avg - ($i - 1), 0), 1) * 100;
        }
        return $fills;
    }

    protected function emptyState(): array
    {
        return [
            'rating_enabled'     => false,
            'rating_count'       => 0,
            'review_count'       => 0,
            'rating_avg'         => 0.0,
            'rating_avg_display' => 0,
            'rating_label'       => '',
            'rating_fills'       => [],
            'rating_html'        => '',
            'review_link_html'   => '',
            'comments_open'      => false,
        ];
    }
}