<h1 class="text-2xl font-bold mb-6">{!! $title !!}</h1>

@php do_action('woocommerce_before_account_orders', $has_orders); @endphp

@if ($has_orders)

    <div class="rounded-lg border border-b-0 overflow-auto shadow-sm">
        <table
            class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table table">
            <thead class="thead">
                <tr>
                    @foreach (wc_get_account_orders_columns() as $column_id => $column_name)
                         @php 
                            $extraClass = $column_id === 'order-actions' ? 'text-right' : '';
                        @endphp

                        <th scope="col"
                            class="woocommerce-orders-table__header woocommerce-orders-table__header-{{ esc_attr($column_id) }} th {{ $extraClass }}">
                            <span class="nobr">{{ esc_html($column_name) }}</span>
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach ($customer_orders->orders as $customer_order)
                    @php
                        /** @var \WC_Order $order */
                        $order = wc_get_order($customer_order);
                        $item_count = $order->get_item_count() - $order->get_item_count_refunded();
                    @endphp

                    <tr
                        class="woocommerce-orders-table__row woocommerce-orders-table__row--status-{{ esc_attr($order->get_status()) }} order">
                        @foreach (wc_get_account_orders_columns() as $column_id => $column_name)
                            @php $is_order_number = ($column_id === 'order-number'); @endphp

                            @php 
                                $extraClass = $column_id === 'order-actions' ? 'text-right' : '';
                            @endphp

                            @if ($is_order_number)
                                <th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-{{ esc_attr($column_id) }} td text-left"
                                    data-title="{{ esc_attr($column_name) }}" scope="row">
                            @else
                                    <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-{{ esc_attr($column_id) }} td {{ $extraClass }}"
                                        data-title="{{ esc_attr($column_name) }}">
                                @endif

                                @if (has_action('woocommerce_my_account_my_orders_column_' . $column_id))
                                    @php do_action('woocommerce_my_account_my_orders_column_' . $column_id, $order); @endphp

                                @elseif ($is_order_number)
                                    <a href="{{ esc_url($order->get_view_order_url()) }}"
                                        aria-label="{{ esc_attr(sprintf(__('View order number %s', 'woocommerce'), $order->get_order_number())) }}">
                                        {{ esc_html(_x('#', 'hash before order number', 'woocommerce') . $order->get_order_number()) }}
                                    </a>

                                @elseif ($column_id === 'order-date')
                                    <time datetime="{{ esc_attr($order->get_date_created()->date('c')) }}">
                                        {{ esc_html(wc_format_datetime($order->get_date_created())) }}
                                    </time>

                                @elseif ($column_id === 'order-status')
                                    {{ esc_html(wc_get_order_status_name($order->get_status())) }}

                                @elseif ($column_id === 'order-total')
                                        {!! wp_kses_post(sprintf(
                                        _n('%1$s for %2$s item', '%1$s for %2$s items', $item_count, 'woocommerce'),
                                        $order->get_formatted_order_total(),
                                        $item_count
                                    )) !!}

                                @elseif ($column_id === 'order-actions')
                                    @php $actions = wc_get_account_orders_actions($order); @endphp
                                    @if (!empty($actions))
                                        @foreach ($actions as $key => $action)
                                            @php
                                                $action_aria_label = empty($action['aria-label'])
                                                    ? sprintf(__('%%1$s order number %%2$s', 'woocommerce'), $action['name'], $order->get_order_number())
                                                    : $action['aria-label'];
                                            @endphp
                                            <a href="{{ esc_url($action['url']) }}"
                                                class="{{ esc_attr($wp_button_class) }} {{ sanitize_html_class($key) }} btn btn-primary btn-sm"
                                                aria-label="{{ esc_attr($action_aria_label) }}">
                                                {{ esc_html($action['name']) }}
                                                <x-lucide-chevron-right aria-hidden="true" />
                                            </a>
                                        @endforeach
                                    @endif
                                @endif

                                @if ($is_order_number)
                                    </th>
                                @else
                                    </td>
                                @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @php do_action('woocommerce_before_account_orders_pagination'); @endphp

    @if (1 < $customer_orders->max_num_pages)
        <div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
            @if (1 !== $current_page)
                <a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button{{ esc_attr($wp_button_class) }}"
                    href="{{ esc_url(wc_get_endpoint_url('orders', $current_page - 1)) }}">
                    {{ __('Previous', 'woocommerce') }}
                </a>
            @endif

            @if (intval($customer_orders->max_num_pages) !== $current_page)
                <a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button{{ esc_attr($wp_button_class) }}"
                    href="{{ esc_url(wc_get_endpoint_url('orders', $current_page + 1)) }}">
                    {{ __('Next', 'woocommerce') }}
                </a>
            @endif
        </div>
    @endif

@else
    <x-alert class="cursor-default">
        <x-lucide-info aria-hidden="true" />
        <x-alert.title>
            {!! esc_html__('Heads Up', 'wordpress-quickstart') !!}
        </x-alert.title>
        <x-alert.description>
            {!! esc_html__('You have not made any purchases yet.', 'woocommerce') !!}
        </x-alert.description>

        <x-alert.actions>
            <a class="btn btn-primary btn-sm"
                href="{{ esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))) }}">
                <x-lucide-store aria-hidden="true" />
                {!! esc_html__('Browse products', 'woocommerce') !!}
            </a>
        </x-alert.actions>
    </x-alert>
@endif

@php do_action('woocommerce_after_account_orders', $has_orders); @endphp