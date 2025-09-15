<?php

namespace App\View\Composers\WooCommerce\SingleProduct;

use Roots\Acorn\View\Composer;

class UpSells extends Composer
{
    protected static $views = [
        // match your Blade view path below
        'woocommerce.single-product.up-sells',
    ];

    public function with(): array
    {
        $product       = $this->resolveCurrentProduct();
        $upsells       = $this->data->get('upsells') ?? [];
        $hasUpsells    = !empty($upsells);
        $productName   = $product ? wp_strip_all_tags($product->get_name()) : '';
        $upsellCount   = $hasUpsells ? count($upsells) : 0;

        // Detect “added to cart” success for this request (non-AJAX).
        $addedNow = false;
        if ($hasUpsells && $product && function_exists('wc_notice_count') && function_exists('wc_get_notices')) {
            if (wc_notice_count('success') > 0) {
                $successNotices = wc_get_notices('success') ?? [];
                foreach ($successNotices as $notice) {
                    $text = is_array($notice) ? ($notice['notice'] ?? '') : $notice;
                    if (!is_string($text)) { continue; }
                    $hasAddedPhrase   = (stripos($text, 'added to your cart') !== false) || (stripos($text, 'has been added to your cart') !== false);
                    $mentionsProduct  = $productName ? (stripos(wp_strip_all_tags($text), $productName) !== false) : true;
                    if ($hasAddedPhrase && $mentionsProduct) {
                        $addedNow = true;
                        break;
                    }
                }
            }
            // Common redirect pattern ?add-to-cart=ID
            if (!$addedNow && isset($_GET['add-to-cart'])) {
                $addedNow = (int) $_GET['add-to-cart'] === (int) $product->get_id();
            }
        }

        // Heading + lead copy
        $rawHeading   = apply_filters('woocommerce_product_upsells_products_heading', __('You may also like&hellip;', 'woocommerce'));
        $smartHeading = ($rawHeading && $rawHeading !== __('You may also like&hellip;', 'woocommerce'))
            ? $rawHeading
            : __('Great choice! Complete your order', 'woocommerce');

        $lead = sprintf(
            /* translators: 1: product name, 2: number of suggestions */
            _n(
                'Based on your selection of “%1$s”, here’s one item we think pairs nicely.',
                'Based on your selection of “%1$s”, here are %2$d items we think pair nicely.',
                $upsellCount,
                'woocommerce'
            ),
            esc_html($productName),
            $upsellCount
        );

        // CTA URLs
        $cartUrl     = function_exists('wc_get_cart_url') ? wc_get_cart_url() : '#';
        $checkoutUrl = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : '#';

        return [
            // gating
            'show_modal'        => ($hasUpsells && $addedNow),
            // content
            'upsells'           => $upsells,
            'smart_heading'     => $smartHeading,
            'lead'              => $lead,
            'product_name'      => $productName,
            'upsell_count'      => $upsellCount,
            'cart_url'          => $cartUrl,
            'checkout_url'      => $checkoutUrl,
        ];
    }

    protected function resolveCurrentProduct()
    {
        $passed = $this->data->get('product');
        if ($passed && is_object($passed) && method_exists($passed, 'get_id')) {
            return $passed;
        }
        if (!empty($GLOBALS['product']) && is_object($GLOBALS['product'])) {
            return $GLOBALS['product'];
        }
        if (function_exists('wc_get_product')) {
            return wc_get_product(get_the_ID());
        }
        return null;
    }
}