@if ($is_item_visible)
    <tr
        class="{{ esc_attr(apply_filters('woocommerce_order_item_class', 'woocommerce-table__line-item order_item', $item, $order)) }}">
        <td class="woocommerce-table__product-name product-name td">
            <div class="flex items-center gap-4">
                <div class="h-full flex items-center justify-center gap-2">
                    <x-modal :title="$product ? $product->get_title() : $item->get_name()" :modalId="'order-item-' . $item_id">
                        <x-slot:trigger>
                            <x-tooltip>
                                <x-slot:trigger>
                                    <button type="button" class="btn btn-outline btn-icon" data-modal-open>
                                        <span
                                            class="sr-only">{{ esc_html__('View product', 'wordpress-quickstart') }}</span>
                                        <x-lucide-package-search aria-hidden="true" />
                                    </button>
                                </x-slot:trigger>

                                <x-slot:content>
                                    {{ esc_html__('View product', 'wordpress-quickstart') }}
                                </x-slot:content>
                            </x-tooltip>
                        </x-slot:trigger>

                        <x-slot:body>
                            <div class="space-y-4">
                                @php do_action('woocommerce_order_item_meta_start', $item_id, $item, $order, false); @endphp
                                @php wc_display_item_meta($item); @endphp
                                @php do_action('woocommerce_order_item_meta_end', $item_id, $item, $order, false); @endphp

                                @if ($show_purchase_note && $purchase_note)
                                    <div class="border-t pt-4 text-sm prose prose-sm">
                                        {!! wpautop(do_shortcode(wp_kses_post($purchase_note))) !!}
                                    </div>
                                @endif
                            </div>
                        </x-slot:body>
                    </x-modal>
                </div>

                <div class="h-full flex items-center justify-center [&_a]:no-underline! [&_a]:hover:underline! ">
                    @if ($product && $product->get_permalink())
                        <a href="{!! esc_url($product->get_permalink()) !!}">{!! esc_html($product->get_title()) !!}</a>
                    @elseif ($product)
                        {!! esc_html($product->get_title()) !!}
                    @else
                        {!! esc_html($item->get_name()) !!}
                    @endif
                </div>
            </div>
        </td>

        <td class="td">
            {!! apply_filters(
            'woocommerce_order_item_quantity_html',
            ' <span class="product-quantity">' . sprintf('&times;&nbsp;%s', $qty_display) . '</span>',
            $item
        ) !!}
        </td>

        <td class="woocommerce-table__product-total product-total td text-right">
            {!! $order->get_formatted_line_subtotal($item) !!}
        </td>
    </tr>
@endif