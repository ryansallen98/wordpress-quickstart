<?php

namespace App\View\Composers\WooCommerce\Checkout;

use Roots\Acorn\View\Composer;
use WC_Order;
use WC_Order_Item_Product;

class FormPay extends Composer
{
    protected static $views = [
        'woocommerce.checkout.form-pay',
        // if you ever render the partial directly, you can add it here too:
        // 'woocommerce.checkout.partials.order',
    ];

    public function with(): array
    {
        $order = $this->resolveOrder();

        return [
            'order'      => $order,
            'items'      => $order ? $this->mapItems($order) : [],
            'returnUrl'  => get_site_url(),
            'returnText' => __('Go Home', 'wordpress-quickstart'),
        ];
    }

    /** Resolve the WC_Order for the /order-pay/ endpoint safely. */
    protected function resolveOrder(): ?WC_Order
    {
        // Woo stores order-pay id in query vars; also validate by order key if present
        $orderId = absint(get_query_var('order-pay'));
        if (! $orderId) {
            $orderKey = isset($_GET['key']) ? sanitize_text_field(wp_unslash($_GET['key'])) : '';
            if ($orderKey) {
                $orderId = wc_get_order_id_by_order_key($orderKey);
            }
        }

        if (! $orderId) {
            return null;
        }

        $order = wc_get_order($orderId);
        if (! $order instanceof WC_Order) {
            return null;
        }

        // If key provided, confirm it matches this order (prevents leaking)
        if (! empty($orderKey ?? null) && $order->get_order_key() !== $orderKey) {
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
            $qty  = max(1, (int) $item->get_quantity());
            $unit = $order->get_prices_include_tax()
                ? (((float) $item->get_total() + (float) $item->get_total_tax()) / $qty)
                : (((float) $item->get_total()) / $qty);
            return wc_price($unit, ['currency' => $order->get_currency()]);
        };

        $items = [];
        foreach ($order->get_items() as $item_id => $item) {
            if (! apply_filters('woocommerce_order_item_visible', true, $item)) {
                continue;
            }

            /** @var WC_Order_Item_Product $item */
            $_product  = $item->get_product();
            $quantity  = (int) $item->get_quantity();
            $thumbnail = $_product ? $_product->get_image('woocommerce_thumbnail') : '';

            // Resolve display name (handle variations)
            if ($_product && $_product->is_type('variation')) {
                $parent       = wc_get_product($_product->get_parent_id());
                $displayName  = $parent ? $parent->get_name() : $item->get_name();
            } else {
                $displayName = $item->get_name();
            }

            $items[] = (object) [
                'id'                => $item_id,
                'quantity'          => $quantity,
                'thumbnail'         => wp_kses_post($thumbnail), // HTML
                'name'              => wp_kses_post(apply_filters('woocommerce_order_item_name', $displayName, $item, false)), // HTML
                'attributes'        => $getItemAttributes($item),
                'short_description' => $_product ? wp_kses_post(wp_trim_words($_product->get_short_description(), 12, '…')) : null, // HTML
                'subtotal'          => $order->get_formatted_line_subtotal($item), // HTML
                'unit_price'        => $getUnitPriceHtml($order, $item),          // HTML
                '_wc_item'          => $item, // keep original for hooks
            ];
        }

        return array_values($items);
    }

    
}