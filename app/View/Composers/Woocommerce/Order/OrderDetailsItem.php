<?php

namespace App\View\Composers\WooCommerce\Order;

use Roots\Acorn\View\Composer;

class OrderDetailsItem extends Composer
{
    /**
     * The views this composer applies to.
     *
     * @var array
     */
    protected static $views = [
        'woocommerce.order.order-details-item',
    ];

    /**
     * Bind data to the view.
     *
     * @return array
     */
    public function with(): array
    {
        $data = $this->view->getData();

        /** @var \WC_Order|null $order */
        $order = $data['order'] ?? null;

        /** @var \WC_Order_Item_Product|null $item */
        $item = $data['item'] ?? null;

        /** @var int|null $item_id */
        $item_id = $data['item_id'] ?? null;

        /** @var \WC_Product|null $product */
        $product = $data['product'] ?? null;

        $show_purchase_note = $data['show_purchase_note'] ?? false;
        $purchase_note = $data['purchase_note'] ?? null;

        // Defaults
        $output = [
            'is_item_visible' => false,
            'is_visible' => false,
            'product_permalink' => '',
            'qty' => 0,
            'refunded_qty' => 0,
            'qty_display' => '',
            'show_purchase_note' => $show_purchase_note,
            'purchase_note' => $purchase_note,
        ];

        if (!$order || !$item || $item_id === null) {
            return $output;
        }

        if (!apply_filters('woocommerce_order_item_visible', true, $item)) {
            return $output;
        }

        $is_visible = $product && $product->is_visible();

        $product_permalink = apply_filters(
            'woocommerce_order_item_permalink',
            $is_visible ? $product->get_permalink($item) : '',
            $item,
            $order
        );

        $qty = $item->get_quantity();
        $refunded_qty = $order->get_qty_refunded_for_item($item_id);

        if ($refunded_qty) {
            $qty_display = '<del>' . esc_html($qty) . '</del> <ins>' . esc_html($qty - ($refunded_qty * -1)) . '</ins>';
        } else {
            $qty_display = esc_html($qty);
        }

        return [
            'is_item_visible' => true,
            'is_visible' => $is_visible,
            'product_permalink' => $product_permalink,
            'qty' => $qty,
            'refunded_qty' => $refunded_qty,
            'qty_display' => $qty_display,
            'show_purchase_note' => $show_purchase_note,
            'purchase_note' => $purchase_note,
        ];
    }
}