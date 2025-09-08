<?php

namespace App\View\Composers\WooCommerce\Checkout;

use Roots\Acorn\View\Composer;
use WC_Order;
use WC_Order_Item_Product;
use WC_Product;
use stdClass;

class ThankYou extends Composer
{
    protected static $views = [
        'woocommerce.checkout.thankyou',
    ];

    public function with(): array
    {
        $order = $this->getOrder();

        return [
            'returnUrl' => get_site_url(),
            'returnText' => __('Go Home', 'wordpress-quickstart'),
            'order' => $order, // handy to have in the view
            'items' => $order ? $this->buildOrderItems($order) : [],
            'subtotals'   => $order ? $this->buildOrderSubtotals($order) : [],
            'order_total' => $order ? $order->get_formatted_order_total() : '',
        ];
    }

    protected function getOrder(): ?WC_Order
    {
        $order_id = absint(get_query_var('order-received'));
        if (!$order_id)
            return null;

        $order = wc_get_order($order_id);
        return $order instanceof WC_Order ? $order : null;
    }

    /**
     * Map WC order item totals to your view model (no remove links on Thank You).
     *
     * @return array<int, stdClass>
     */
    protected function buildOrderSubtotals(WC_Order $order): array
    {
        $rows = $order->get_order_item_totals(); // label/value pairs (HTML)
        if (empty($rows) || !is_array($rows))
            return [];

        $out = [];
        foreach ($rows as $key => $row) {
            // Skip the grand total here; you render it separately as $order_total
            if ($key === 'order_total') {
                continue;
            }

            $o = new stdClass();
            $o->label = wp_strip_all_tags($row['label'] ?? '');
            $o->value = $row['value'] ?? '';
            $o->prefix = '';       // optional prefix text (you show it if present)
            $o->description = '';       // optional helper/description line
            $o->isCoupon = ($key === 'discount'); // but we won't show remove on Thank You
            $o->code = '';       // no code/remove on Thank You
            $o->sanitizedCode = '';
            $o->removeUrl = '';

            $out[] = $o;
        }

        return $out;
    }

    /**
     * Build item view-models as objects (so Blade `$item->...` works).
     *
     * @return array<int, stdClass>
     */
    protected function buildOrderItems(WC_Order $order): array
    {
        $out = [];

        /** @var array<int, WC_Order_Item_Product> $line_items */
        $line_items = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));

        foreach ($line_items as $item_id => $wc_item) {
            if (!$wc_item instanceof WC_Order_Item_Product) {
                continue;
            }

            /** @var WC_Product|null $product */
            $product = $wc_item->get_product();
            $visible = $product && $product->is_visible();
            $name = $wc_item->get_name();
            $permalink = $visible ? $product->get_permalink() : '';
            $qty = (int) $wc_item->get_quantity();

            // Build object with properties your Blade partial uses
            $vm = new stdClass();
            $vm->id = (int) $item_id;
            $vm->_wc_item = $wc_item; // keep original WC item for hooks
            $vm->quantity = $qty;
            $vm->thumbnail = $product ? $product->get_image('woocommerce_thumbnail', ['class' => 'w-full h-full object-cover']) : '';
            $vm->name = $permalink
                ? sprintf('<a href="%s" class="hover:underline">%s</a>', esc_url($permalink), esc_html($name))
                : esc_html($name);

            // Variation/meta as label/value pairs
            $vm->attributes = [];
            foreach ($wc_item->get_formatted_meta_data() as $meta) {
                $vm->attributes[] = [
                    'label' => wp_kses_post($meta->display_key),
                    'value' => wp_kses_post(force_balance_tags($meta->display_value)),
                ];
            }

            $vm->short_description = $product ? $product->get_short_description() : '';

            // Subtotal (Woo formats per tax display settings)
            $vm->subtotal = $order->get_formatted_line_subtotal($wc_item);

            // Unit price = line total / qty (avoid /0), formatted in order currency
            $unit_total = (float) $wc_item->get_total();
            $unit_tax = (float) $wc_item->get_total_tax();
            $units = max(1, $qty);

            // Match Woo display: if order displays prices incl tax, include per-unit tax too
            $display_incl_tax = wc_prices_include_tax(); // or: $order->get_prices_include_tax()
            $unit_amount = $display_incl_tax
                ? ($unit_total + $unit_tax) / $units
                : $unit_total / $units;

            $vm->unit_price = wc_price($unit_amount, ['currency' => $order->get_currency()]);

            $out[] = $vm;
        }

        return $out;
    }
}