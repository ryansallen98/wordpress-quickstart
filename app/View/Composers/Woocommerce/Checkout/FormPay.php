<?php

namespace App\View\Composers\WooCommerce\Checkout;

use Roots\Acorn\View\Composer;
use WC_Order;
use WC_Order_Item_Product;

class FormPay extends Composer
{
    protected static $views = [
        'woocommerce.checkout.form-pay',
    ];

    public function with(): array
    {
        $order = $this->resolveOrder();

        return [
            'order' => $order,
            'items' => $order ? $this->mapItems($order) : [],
            'subtotals' => $order ? $this->mapOrderSubtotals($order) : [],
            'order_total' => $order ? $this->getOrderTotal($order) : '',
            'returnUrl' => get_site_url(),
            'returnText' => __('Go Home', 'wordpress-quickstart'),
        ];
    }

    /** Resolve the WC_Order for the /order-pay/ endpoint safely. */
    protected function resolveOrder(): ?WC_Order
    {
        // Woo stores order-pay id in query vars; also validate by order key if present
        $orderId = absint(get_query_var('order-pay'));
        if (!$orderId) {
            $orderKey = isset($_GET['key']) ? sanitize_text_field(wp_unslash($_GET['key'])) : '';
            if ($orderKey) {
                $orderId = wc_get_order_id_by_order_key($orderKey);
            }
        }

        if (!$orderId) {
            return null;
        }

        $order = wc_get_order($orderId);
        if (!$order instanceof WC_Order) {
            return null;
        }

        // If key provided, confirm it matches this order (prevents leaking)
        if (!empty($orderKey ?? null) && $order->get_order_key() !== $orderKey) {
            return null;
        }

        return $order;
    }

    /** Transform Woo items to the shape expected by your partial. */
    protected function mapItems(WC_Order $order): array
    {
        $getItemAttributes = function (WC_Order_Item_Product $item): array {
            $out = [];
            foreach ($item->get_formatted_meta_data('') as $meta) {
                $label = isset($meta->display_key) ? wp_strip_all_tags((string) $meta->display_key) : '';
                $value = isset($meta->display_value) ? wp_strip_all_tags((string) $meta->display_value) : '';
                if ($label !== '' && $value !== '') {
                    $out[] = ['label' => $label, 'value' => $value];
                }
            }
            return $out;
        };

        $getUnitPriceHtml = function (WC_Order $order, WC_Order_Item_Product $item): string {
            $qty = max(1, (int) $item->get_quantity());
            $unit = $order->get_prices_include_tax()
                ? (((float) $item->get_total() + (float) $item->get_total_tax()) / $qty)
                : (((float) $item->get_total()) / $qty);
            return wc_price($unit, ['currency' => $order->get_currency()]);
        };

        $items = [];
        foreach ($order->get_items() as $item_id => $item) {
            if (!apply_filters('woocommerce_order_item_visible', true, $item)) {
                continue;
            }

            /** @var WC_Order_Item_Product $item */
            $_product = $item->get_product();
            $quantity = (int) $item->get_quantity();
            $thumbnail = $_product ? $_product->get_image('woocommerce_thumbnail') : '';

            // Resolve display name (handle variations)
            if ($_product && $_product->is_type('variation')) {
                $parent = wc_get_product($_product->get_parent_id());
                $displayName = $parent ? $parent->get_name() : $item->get_name();
            } else {
                $displayName = $item->get_name();
            }

            $items[] = (object) [
                'id' => $item_id,
                'quantity' => $quantity,
                'thumbnail' => wp_kses_post($thumbnail), // HTML
                'name' => wp_kses_post(apply_filters('woocommerce_order_item_name', $displayName, $item, false)), // HTML
                'attributes' => $getItemAttributes($item),
                'short_description' => $_product ? wp_kses_post(wp_trim_words($_product->get_short_description(), 12, '…')) : null, // HTML
                'subtotal' => $order->get_formatted_line_subtotal($item), // HTML
                'unit_price' => $getUnitPriceHtml($order, $item),          // HTML
                '_wc_item' => $item, // keep original for hooks
            ];
        }

        return array_values($items);
    }

    protected function getOrderTotal(\WC_Order $order): string
    {
        // Includes currency and tax display text (e.g. “includes £X VAT”) just like core
        return $order->get_formatted_order_total();
        // If you want just the number with currency (no extra text), use:
        // return wc_price( $order->get_total(), ['currency' => $order->get_currency()] );
    }

    protected function mapOrderSubtotals(\WC_Order $order): array
    {
        $out = [];

        // Subtotal
        $out[] = (object) [
            'key' => 'subtotal',
            'type' => 'subtotal',
            'label' => esc_html__('Subtotal', 'woocommerce'),
            'value' => wc_price($order->get_subtotal(), ['currency' => $order->get_currency()]),
            'prefix' => '',
            'isCoupon' => false,
            'isShipping' => false,
            'description' => '',
        ];

        // Coupons / Discounts (order context – no remove links on order-pay)
        // Woo stores total discount excl. tax; build per-coupon display lines if available.
        $applied = $order->get_coupon_codes(); // array of codes
        foreach ($applied as $code) {
            $sanitized = sanitize_title($code);
            // Order doesn't expose per-coupon discount easily; show total discount as negative
            // If you track per-coupon in meta, you can replace the amount below.
            $amount = 0.0;
            foreach ($order->get_items('coupon') as $coupon_item) {
                if (strcasecmp($coupon_item->get_code(), $code) === 0) {
                    $amount = (float) $coupon_item->get_discount() + (float) $coupon_item->get_discount_tax();
                    break;
                }
            }
            $amountHtml = '-' . wc_price($amount, ['currency' => $order->get_currency()]);

            // Label like "Coupon: CODE"
            $labelHtml = sprintf(
                /* translators: %s: coupon code */
                esc_html__('Coupon: %s', 'woocommerce'),
                esc_html($code)
            );

            $out[] = (object) [
                'key' => 'coupon_' . $sanitized,
                'type' => 'coupon',
                'label' => $labelHtml,         // plain text is fine here
                'value' => $amountHtml,        // HTML
                'prefix' => '',
                'isCoupon' => true,
                'code' => $code,
                'sanitizedCode' => $sanitized,
                'removeUrl' => null,               // no removal on order-pay
                'isShipping' => false,
                'description' => '',
            ];
        }

        // Shipping (order items of type "shipping")
        $has_shipping_items = !empty($order->get_items('shipping'));
        $needs_shipping = $order->needs_shipping_address();

        // Only append a Shipping row when it matters
        if ($has_shipping_items || $needs_shipping) {
            $shipping_total = (float) $order->get_shipping_total();
            $shipping_tax = (float) $order->get_shipping_tax();
            $amount = $order->get_prices_include_tax()
                ? $shipping_total + $shipping_tax
                : $shipping_total;

            // Consider 0.00 valid if a method exists (e.g., Free shipping / Local pickup)
            $has_amount = ($amount > 0) || ($has_shipping_items && $amount === 0.0);

            $valueHtml = $has_amount
                ? \wc_price($amount, ['currency' => $order->get_currency()])
                : \esc_html__('N/A', 'woocommerce');

            // Description = chosen shipping method(s) or guidance
            $desc = '';
            if ($has_shipping_items) {
                $titles = array_filter(array_map(fn($i) => $i->get_method_title(), $order->get_items('shipping')));
                $desc = implode(', ', $titles);
            } elseif (!$has_amount) {
                $desc = \esc_html__('No shipping method selected.', 'woocommerce');
            }

            $out[] = (object) [
                'key' => 'shipping',
                'type' => 'shipping',
                'label' => \esc_html__('Shipping', 'woocommerce'),
                'value' => $valueHtml,
                'prefix' => '',
                'isCoupon' => false,
                'isShipping' => true,
                'description' => $desc,
            ];
        }

        // Fees
        foreach ($order->get_fees() as $fee) {
            $out[] = (object) [
                'key' => 'fee_' . sanitize_title($fee->get_name()),
                'type' => 'fee',
                'label' => esc_html($fee->get_name()),
                'value' => wc_price($fee->get_total() + $fee->get_total_tax(), ['currency' => $order->get_currency()]),
                'prefix' => '',
                'isCoupon' => false,
                'isShipping' => false,
                'description' => '',
            ];
        }

        // Taxes (when prices are excl. tax, show itemized or total like checkout)
        if (wc_tax_enabled() && !$order->get_prices_include_tax()) {
            $tax_totals = $order->get_tax_totals(); // array of objects with ->label and ->formatted_amount
            if (!empty($tax_totals)) {
                foreach ($tax_totals as $code => $tax) {
                    $out[] = (object) [
                        'key' => 'tax_' . sanitize_title($code),
                        'type' => 'tax',
                        'label' => $tax->label,
                        'value' => $tax->formatted_amount, // HTML string
                        'prefix' => '',
                        'isCoupon' => false,
                        'isShipping' => false,
                        'description' => '',
                    ];
                }
            }
        }

        return $out;
    }
}