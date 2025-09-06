<?php

namespace App\View\Composers\WooCommerce\Checkout;

use Roots\Acorn\View\Composer;

class ReviewOrder extends Composer
{
    protected static $views = [
        'woocommerce.checkout.review-order',
    ];

    public function with(): array
    {
        return [
            'cart_items' => $this->mapCartItems(),
            'subtotals' => $this->mapSubtotals(),
            'order_total' => $this->getCartTotal(),
        ];
    }

    protected function getCartTotal(): string
    {
        ob_start();
        wc_cart_totals_order_total_html(); // echoes
        return trim(ob_get_clean());       // return as string for the view
    }

    protected function mapCartItems(): array
    {
        if (!function_exists('WC') || !WC()->cart) {
            return [];
        }

        $items = [];

        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            /** @var WC_Product|null $product */
            $product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
            $visible = apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key);
            $qty = (int) ($cart_item['quantity'] ?? 0);

            if (!$product || !$product->exists() || $qty < 1 || !$visible) {
                continue;
            }

            // Thumbnail (HTML)
            $thumbnail = apply_filters(
                'woocommerce_cart_item_thumbnail',
                $product->get_image('woocommerce_thumbnail'),
                $cart_item,
                $cart_item_key
            );

            // Name (handle variations + allow filters)
            if ($product->is_type('variation')) {
                $parent = wc_get_product($product->get_parent_id());
                $baseName = $parent ? $parent->get_name() : $product->get_name();
            } else {
                $baseName = $product->get_name();
            }
            $nameHtml = apply_filters('woocommerce_cart_item_name', $baseName, $cart_item, $cart_item_key);

            // Attributes (plain array: [{label, value}, ...])
            $attributes = [];
            $itemData = function_exists('wc_get_item_data') ? wc_get_item_data($cart_item) : [];
            if (!empty($itemData)) {
                foreach ($itemData as $data) {
                    $label = isset($data['key']) ? wc_clean(wp_strip_all_tags($data['key'])) : '';
                    $value = isset($data['display'])
                        ? wc_clean(wp_strip_all_tags($data['display']))
                        : (isset($data['value']) ? wc_clean(wp_strip_all_tags($data['value'])) : '');
                    if ($label !== '' && $value !== '') {
                        $attributes[] = ['label' => $label, 'value' => $value];
                    }
                }
            } elseif (!empty($cart_item['variation'])) {
                foreach ($cart_item['variation'] as $attrKey => $attrValue) {
                    if (!$attrValue)
                        continue;
                    $taxonomy = str_replace('attribute_', '', $attrKey);
                    $label = function_exists('wc_attribute_label')
                        ? wc_attribute_label($taxonomy, $product)
                        : ucwords(str_replace(['pa_', '_', '-'], ['', ' ', ' '], $taxonomy));
                    if (taxonomy_exists($taxonomy)) {
                        $term = get_term_by('slug', $attrValue, $taxonomy);
                        $value = $term && !is_wp_error($term) ? $term->name : wc_clean($attrValue);
                    } else {
                        $value = wc_clean($attrValue);
                    }
                    $attributes[] = ['label' => $label, 'value' => $value];
                }
            }

            // Short description (HTML, sanitized)
            $short = wp_kses_post(wp_trim_words($product->get_short_description(), 12, '…'));

            // Subtotal (HTML, respects tax display settings)
            $subtotalHtml = apply_filters(
                'woocommerce_cart_item_subtotal',
                WC()->cart->get_product_subtotal($product, $qty),
                $cart_item,
                $cart_item_key
            );

            // Unit price (HTML, matches cart display mode)
            $displayInclTax = WC()->cart->display_prices_including_tax();
            if ($displayInclTax) {
                $unit = wc_get_price_including_tax($product);
            } else {
                $unit = wc_get_price_excluding_tax($product);
            }
            $unitHtml = wc_price($unit);

            $rowClass = esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key));

            $items[] = (object) [
                'id' => $cart_item_key,
                'row_class' => $rowClass,
                'quantity' => $qty,
                'thumbnail' => wp_kses_post($thumbnail),          // HTML
                'name' => wp_kses_post($nameHtml),           // HTML
                'attributes' => $attributes,                       // array
                'short_description' => $short,                            // HTML
                'subtotal' => $subtotalHtml,                     // HTML
                'unit_price' => $unitHtml,                         // HTML
                // originals if you ever need them:
                '_cart_item' => $cart_item,
                '_product' => $product,
            ];
        }

        return array_values($items);
    }

    protected function mapSubtotals(): array
    {
        if (!function_exists('WC') || !WC()->cart) {
            return [];
        }

        $cart = WC()->cart;
        $out = [];

        // Subtotal
        ob_start();
        wc_cart_totals_subtotal_html();
        $subtotalHtml = trim(ob_get_clean());
        $out[] = (object) [
            'key' => 'subtotal',
            'type' => 'subtotal',
            'label' => esc_html__('Subtotal', 'woocommerce'),
            'value' => $subtotalHtml, // HTML
            'prefix' => '',
            'isCoupon' => false,
            'isShipping' => false,
        ];

        // Coupons
        foreach ($cart->get_coupons() as $code => $coupon) {
            // label html
            ob_start();
            wc_cart_totals_coupon_label($coupon);
            $labelHtml = trim(ob_get_clean());

            // amount (respect tax display)
            $discount = $cart->get_coupon_discount_amount($code);
            $discount_tax = $cart->get_coupon_discount_tax_amount($code);
            $display = $cart->display_prices_including_tax() ? ($discount + $discount_tax) : $discount;

            $amountHtml = '-' . wc_price($display);
            /** Allow extensions to adjust amount HTML (parity with core) */
            $amountHtml = apply_filters('woocommerce_coupon_discount_amount_html', $amountHtml, $coupon);

            // remove link + sanitized code
            $removeUrl = function_exists('wc_get_cart_remove_coupon_url')
                ? esc_url(wc_get_cart_remove_coupon_url($code))
                : esc_url(wp_nonce_url(add_query_arg(['remove_coupon' => rawurlencode($code)], wc_get_cart_url()), 'woocommerce-cart'));
            $sanitizedCode = esc_attr(sanitize_title($coupon->get_code()));

            $out[] = (object) [
                'key' => 'coupon_' . $sanitizedCode,
                'type' => 'coupon',
                'label' => $labelHtml,  // HTML
                'value' => $amountHtml, // HTML
                'prefix' => '',
                'isCoupon' => true,
                'code' => $coupon->get_code(),
                'sanitizedCode' => $sanitizedCode,
                'removeUrl' => $removeUrl,
                'isShipping' => false,
            ];
        }

        // Shipping (if shown)
        if ($cart->needs_shipping() && $cart->show_shipping()) {
            $packages = \WC()->shipping()->get_packages();
            $chosen = \WC()->session ? (array) \WC()->session->get('chosen_shipping_methods', []) : [];
            $has_rates = false;
            $has_chosen = false;
            $chosen_cost = null;

            foreach ($packages as $i => $package) {
                $rates = isset($package['rates']) ? $package['rates'] : [];
                if (!empty($rates)) {
                    $has_rates = true;
                }
                if (isset($chosen[$i], $rates[$chosen[$i]])) {
                    $has_chosen = true;
                    /** @var \WC_Shipping_Rate $rate */
                    $rate = $rates[$chosen[$i]];
                    $chosen_cost = (float) $rate->get_cost();
                }
            }

            // Amount respecting tax display
            $amount = (float) $cart->get_shipping_total();
            if ($cart->display_prices_including_tax()) {
                $amount += (float) $cart->get_shipping_tax_total();
            }

            // A usable amount exists if > 0 or a chosen method with 0.00 (free)
            $has_amount = ($amount > 0) || ($has_chosen && $chosen_cost === 0.0);
            $valueHtml = $has_amount ? \wc_price($amount) : \esc_html__('', 'woocommerce');

            // Build description: move “no shipping” / guidance text here
            $desc = '';
            if (!$has_rates) {
                if (\function_exists('\wc_no_shipping_available_html')) {
                    // Prefer checkout version, strip tags for plain text
                    $desc = \wp_strip_all_tags(\wc_no_shipping_available_html($packages[0] ?? []));
                } elseif (\function_exists('\wc_cart_no_shipping_available_html')) {
                    // Fallback to cart message if available
                    $desc = \wp_strip_all_tags(\wc_cart_no_shipping_available_html());
                } else {
                    $desc = \esc_html__('There are no shipping options available.', 'woocommerce');
                }
            } elseif ($has_rates && !$has_chosen) {
                $desc = \esc_html__('Select a shipping method to see the price.', 'woocommerce');
            }

            $out[] = (object) [
                'key' => 'shipping',
                'type' => 'shipping',
                'label' => \esc_html__('Shipping', 'woocommerce'),
                'value' => $valueHtml,   // price or "N/A"
                'prefix' => '',
                'isCoupon' => false,
                'isShipping' => true,
                'description' => $desc,        // plain text; your Blade prints this under the row
            ];
        }

        // Fees
        foreach ($cart->get_fees() as $fee) {
            ob_start();
            wc_cart_totals_fee_html($fee);
            $feeHtml = trim(ob_get_clean());

            $out[] = (object) [
                'key' => 'fee_' . sanitize_title($fee->name),
                'type' => 'fee',
                'label' => esc_html($fee->name),
                'value' => $feeHtml,   // HTML
                'prefix' => '',
                'isCoupon' => false,
                'isShipping' => false,
            ];
        }

        // Taxes (only when prices are excl. tax)
        if (wc_tax_enabled() && !$cart->display_prices_including_tax()) {
            if ('itemized' === get_option('woocommerce_tax_total_display')) {
                foreach ($cart->get_tax_totals() as $code => $tax) {
                    $out[] = (object) [
                        'key' => 'tax_' . sanitize_title($code),
                        'type' => 'tax',
                        'label' => $tax->label,
                        'value' => $tax->formatted_amount, // HTML
                        'prefix' => '',
                        'isCoupon' => false,
                        'isShipping' => false,
                    ];
                }
            } else {
                ob_start();
                wc_cart_totals_taxes_total_html();
                $taxTotalHtml = trim(ob_get_clean());

                $out[] = (object) [
                    'key' => 'tax_total',
                    'type' => 'tax_total',
                    'label' => WC()->countries->tax_or_vat(),
                    'value' => $taxTotalHtml, // HTML
                    'prefix' => '',
                    'isCoupon' => false,
                    'isShipping' => false,
                ];
            }
        }

        return $out;
    }
}