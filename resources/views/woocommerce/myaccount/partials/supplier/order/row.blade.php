{{-- resources/views/myaccount/parts/order-row.blade.php --}}
@php
    defined('ABSPATH') || exit;

    use WCSM\Orders\Utils;

    /** @var \WC_Order $order */
    /** @var int $supplier_id */

    $order_id = $order->get_id();
    $number = $order->get_order_number();
    $date = $order->get_date_created()
        ? $order->get_date_created()->date_i18n(wc_date_format() . ' ' . wc_time_format())
        : '';
    $name = trim($order->get_formatted_billing_full_name() ?: $order->get_formatted_shipping_full_name());

    $groups = Utils::group_items_by_supplier($order);
    $items = $groups[$supplier_id] ?? [];

    $ff = Utils::get_fulfilment($order);
    $mine = $ff[$supplier_id] ?? ['status' => 'pending', 'tracking' => ['carrier' => '', 'number' => '', 'url' => '', 'notes' => '']];

    $slip_url = add_query_arg([
        'wcsm_packing_slip' => 1,
        'order_id' => $order_id,
        '_wpnonce' => wp_create_nonce('wcsm_download_slip'),
    ], wc_get_account_endpoint_url(\WCSM\Accounts\SupplierOrdersEndpoint::ENDPOINT));

    $status_opts = [
        'pending' => __('Pending', 'wc-supplier-manager'),
        'received' => __('Received', 'wc-supplier-manager'),
        'sent' => __('Sent', 'wc-supplier-manager'),
        'rejected' => __('Rejected', 'wc-supplier-manager'),
    ];
@endphp

<tr>
    <td class="px-4 py-2 border-b">
        <span>#{{ esc_html($number) }}</span>
    </td>

    <td class="px-4 py-2 border-b whitespace-nowrap">{{ esc_html($date) }}</td>

    <td class="px-4 py-2 border-b">{!! $name ? esc_html($name) : '&mdash;' !!}</td>

    <td class="px-4 py-2 border-b whitespace-nowrap">
        <ul>
            @foreach ($items as $item)
                @php $meta = wc_display_item_meta($item, ['echo' => false]); @endphp
                <li>
                    {{ esc_html($item->get_name()) . ' × ' . esc_html($item->get_quantity()) }}
                    @if ($meta)
                        <div class="wcsm-item-meta">{!! wp_kses_post($meta) !!}</div>
                    @endif
                </li>
            @endforeach
        </ul>
    </td>
    <td class="px-4 py-2 border-b">
        <strong>{{ esc_html($status_opts[$mine['status'] ?? 'pending'] ?? 'Pending') }}</strong>
    </td>
    <td class="text-right px-4 py-2 border-b">
        <div class="flex flex-row gap-2 justify-end">
            <a class="btn btn-primary btn-sm" href="{!! esc_url($slip_url) !!}">
                <x-lucide-file-text />
                {{ esc_html__('Packing slip', 'wc-supplier-manager') }}
            </a>
            <button type="button" class="btn btn-outline btn-sm wcsm-toggle" aria-expanded="false"
                data-target="#wcsm-order-form-{{ esc_attr($order_id) }}">
                <x-lucide-edit-2 />
                {{ esc_html__('Update', 'wc-supplier-manager') }}
            </button>
        </div>
    </td>
</tr>

<tr id="wcsm-order-form-{{ esc_attr($order_id) }}" class="wcsm-order-edit" style="display:none;" aria-hidden="true">
    <td colspan="6">
        @include('woocommerce.myaccount.partials.supplier.order.update-order')
    </td>
</tr>