@php
    /** @var int $order_id */
    /** @var bool $show_downloads */

    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }

    $order_items = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));
    $show_purchase_note = $order->has_status(apply_filters('woocommerce_purchase_note_order_statuses', ['completed', 'processing']));
    $downloads = $order->get_downloadable_items();
    $actions = array_filter(
        wc_get_account_orders_actions($order),
        fn($key) => 'view' !== $key,
        ARRAY_FILTER_USE_KEY
    );

    // The order belongs to current user (or guest order to guest).
    $show_customer_details = ($order->get_user_id() === get_current_user_id());
@endphp

@if (!empty($show_downloads))
    @php
        wc_get_template('order/order-downloads.php', [
            'downloads' => $downloads,
            'show_title' => true,
        ]);
      @endphp
@endif

<section class="woocommerce-order-details">
    @php do_action('woocommerce_order_details_before_order_table', $order); @endphp

    <h2 class="woocommerce-order-details__title font-bold text-xl mb-2">{{ __('Order details', 'woocommerce') }}</h2>

    <div class="rounded-lg shadow-sm overflow-auto mb-6 border border-b-0">
        <table class="woocommerce-table woocommerce-table--order-details shop_table order_details table">
            <thead class="thead">
                <tr>
                    <th class="woocommerce-table__product-name product-name th">{{ __('Product', 'woocommerce') }}</th>
                    <th class="woocommerce-table__product-name product-name th">{{ __('Quantity', 'woocommerce') }}</th>
                    <th class="woocommerce-table__product-table product-total th text-right">{{ __('Total', 'woocommerce') }}</th>
                </tr>
            </thead>

            <tbody>
                @php do_action('woocommerce_order_details_before_order_table_items', $order); @endphp

                @foreach ($order_items as $item_id => $item)
                    @php $product = $item->get_product(); @endphp
                    @php
                        wc_get_template('order/order-details-item.php', [
                            'order' => $order,
                            'item_id' => $item_id,
                            'item' => $item,
                            'show_purchase_note' => $show_purchase_note,
                            'purchase_note' => $product ? $product->get_purchase_note() : '',
                            'product' => $product,
                        ]);
                    @endphp
                @endforeach

                @php do_action('woocommerce_order_details_after_order_table_items', $order); @endphp
            </tbody>

            @if (!empty($actions))
                <tfoot>
                    <tr>
                        <th class="order-actions--heading" colspan="2" class="text-right pr-1 td">{{ __('Actions', 'woocommerce') }}:</th>
                        <td class="td">
                            @php $wp_button_class = wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''; @endphp
                            @foreach ($actions as $key => $action)
                                @php
                                    $action_aria_label = empty($action['aria-label'])
                                        ? sprintf(__('%%1$s order number %%2$s', 'woocommerce'), $action['name'], $order->get_order_number())
                                        : $action['aria-label'];
                                  @endphp
                                <a href="{{ esc_url($action['url']) }}"
                                    class="woocommerce-button{{ esc_attr($wp_button_class) }} button {{ sanitize_html_class($key) }} order-actions-button"
                                    aria-label="{{ esc_attr($action_aria_label) }}">
                                    {{ esc_html($action['name']) }}
                                </a>
                            @endforeach
                        </td>
                    </tr>
                </tfoot>
            @endif

            <tfoot>
                @foreach ($order->get_order_item_totals() as $key => $total)
                    <tr>
                        <th scope="row" colspan="2" class="text-right pr-1 td">{{ esc_html($total['label']) }}</th>
                        <td class="td">{!! wp_kses_post($total['value']) !!}</td>
                    </tr>
                @endforeach

                @if ($order->get_customer_note())
                    <tr>
                        <th colspan="2" class="text-right pr-1 td">{{ __('Note:', 'woocommerce') }}</th>
                        <td class="td">
                            @php
                                $customer_note = wc_wptexturize_order_note($order->get_customer_note());
                                echo wp_kses(nl2br($customer_note), ['br' => []]);
                            @endphp
                        </td>
                    </tr>
                @endif
            </tfoot>
        </table>
    </div>

    @php do_action('woocommerce_order_details_after_order_table', $order); @endphp
</section>

@php
    /**
     * After order details.
     *
     * @since 4.4.0
     * @param \WC_Order $order
     */
    do_action('woocommerce_after_order_details', $order);

    if ($show_customer_details) {
        wc_get_template('order/order-details-customer.php', ['order' => $order]);
    }
@endphp