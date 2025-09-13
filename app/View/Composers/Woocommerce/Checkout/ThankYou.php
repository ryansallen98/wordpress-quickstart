<?php

namespace App\View\Composers\WooCommerce\Checkout;

use App\Helpers\CartAttributeHelper;
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
            'subtotals' => $order ? $this->buildOrderSubtotals($order) : [],
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
     * Build item view-models for order items (Thank You page / order details).
     *
     * Produces:
     * - $vm->attributes: variation selections (plain text)
     * - $vm->custom_attributes: user-entered meta (HTML allowed; may contain <a>)
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

            // View model
            $vm = new stdClass();
            $vm->id = (int) $item_id;
            $vm->_wc_item = $wc_item; // keep original WC item for hooks
            $vm->quantity = $qty;
            $vm->thumbnail = $product ? $product->get_image('woocommerce_thumbnail', ['class' => 'w-full h-full object-cover']) : '';
            $vm->name = $permalink
                ? sprintf('<a href="%s" class="hover:underline">%s</a>', esc_url($permalink), esc_html($name))
                : esc_html($name);

            // --------------------------
            // A) Variation selections -> $vm->attributes (plain text)
            // --------------------------
            $attributes = [];

            // Variation attributes on order items are typically stored as meta with keys like "pa_color", "attribute_pa_size", or attribute labels.
            // We'll classify variation-like meta and render them as plain text.
            $raw_meta = $wc_item->get_meta_data(); // array of WC_Meta_Data
            foreach ($raw_meta as $meta) {
                $key = (string) $meta->get_data()['key'];
                $value = $meta->get_data()['value'];

                // Detect variation attribute keys
                $is_variation_key =
                    strpos($key, 'attribute_') === 0 ||
                    strpos($key, 'pa_') === 0 ||
                    (taxonomy_exists($key) && strpos($key, 'pa_') === 0);

                if ($is_variation_key) {
                    $label = function_exists('wc_attribute_label')
                        ? wc_attribute_label($key, $product)
                        : CartAttributeHelper::cleanLabel($key);

                    // Value may be term slug or human text
                    if (taxonomy_exists($key) && is_string($value)) {
                        $term = get_term_by('slug', $value, $key);
                        $value = $term && !is_wp_error($term) ? $term->name : $value;
                    } elseif (is_array($value)) {
                        $value = implode(', ', array_map('wc_clean', array_map('wp_strip_all_tags', $value)));
                    } else {
                        $value = wc_clean(wp_strip_all_tags((string) $value));
                    }

                    if ($label !== '' && $value !== '') {
                        $attributes[] = ['label' => $label, 'value' => $value];
                    }
                }
            }

            // De-dupe attrs
            $vm->attributes = array_values(array_unique($attributes, SORT_REGULAR));

            // ---------------------------------------------
            // B) User meta (APF/Add-ons/Custom) -> $vm->custom_attributes (HTML)
            // ---------------------------------------------
            $custom_attributes = [];

            // 1) WooCommerce's formatted meta is already user-facing (includes anchors)
            //    get_formatted_meta_data() returns WC_Meta Objects with display_key and display_value (string HTML)
            foreach ($wc_item->get_formatted_meta_data() as $meta) {
                $label = CartAttributeHelper::cleanLabel($meta->display_key);
                $value_html = force_balance_tags((string) $meta->display_value);

                // --- SKIP if this is a variation attribute ---
                $raw_key = strtolower($meta->key ?? '');
                if (
                    str_starts_with($raw_key, 'attribute_') ||
                    str_starts_with($raw_key, 'pa_') ||
                    taxonomy_exists(sanitize_title($raw_key))
                ) {
                    continue; // already handled in $item->attributes
                }

                if ($label !== '' && $value_html !== '') {
                    $value_html = CartAttributeHelper::sanitizeAnchors($value_html);
                    $custom_attributes[] = ['label' => $label, 'value' => $value_html];
                }
            }

            // 2) Raw meta sweep for anything that wasn't formatted (e.g., custom delivery_date)
            //    This catches keys that might not be returned by get_formatted_meta_data()
            $whitelist_extra = ['delivery_date', 'delivery', 'pickup_date', 'pickup_time'];
            foreach ($raw_meta as $meta) {
                $key = (string) $meta->get_data()['key'];
                $value = $meta->get_data()['value'];

                // Skip hidden/internal
                if ($key === '' || $key[0] === '_') {
                    continue;
                }

                // Skip if it looks like a variation key (handled in attributes)
                if (strpos($key, 'attribute_') === 0 || strpos($key, 'pa_') === 0) {
                    continue;
                }

                // Only include if it’s in our whitelist or looks human-facing
                $is_whitelisted = in_array($key, $whitelist_extra, true);
                $looks_human = !preg_match('/^(line_total|line_tax|line_subtotal|line_subtotal_tax|qty|quantity|total|tax)$/i', $key);

                if ($is_whitelisted || $looks_human) {
                    $label = CartAttributeHelper::labelFromKey($key);
                    $value_html = CartAttributeHelper::valueToHtml($value); // HTML with anchors when URLs

                    if ($label !== '' && $value_html !== '') {
                        // Ensure we didn't already add this (compare label+value)
                        $dup = false;
                        foreach ($custom_attributes as $pair) {
                            if ($pair['label'] === $label && $pair['value'] === $value_html) {
                                $dup = true;
                                break;
                            }
                        }
                        if (!$dup) {
                            $custom_attributes[] = ['label' => $label, 'value' => $value_html];
                        }
                    }
                }
            }

            // De-dupe custom attrs
            $vm->custom_attributes = array_values(array_unique($custom_attributes, SORT_REGULAR));

            // Description
            $vm->short_description = $product ? $product->get_short_description() : '';

            // Subtotal formatted the Woo way
            $vm->subtotal = $order->get_formatted_line_subtotal($wc_item);

            // Unit price (respect order tax display)
            $unit_total = (float) $wc_item->get_total();
            $unit_tax = (float) $wc_item->get_total_tax();
            $units = max(1, $qty);

            // Use how the order records prices (incl/excl tax)
            $display_incl_tax = (bool) $order->get_prices_include_tax();
            $unit_amount = $display_incl_tax
                ? ($unit_total + $unit_tax) / $units
                : $unit_total / $units;

            $vm->unit_price = wc_price($unit_amount, ['currency' => $order->get_currency()]);

            $out[] = $vm;
        }

        return $out;
    }
}