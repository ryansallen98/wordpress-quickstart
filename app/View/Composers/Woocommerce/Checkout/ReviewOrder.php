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
        ];
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
}