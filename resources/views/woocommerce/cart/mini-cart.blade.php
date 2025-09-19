@php defined('ABSPATH') || exit; @endphp
@php do_action('woocommerce_before_mini_cart'); @endphp

@if (!$cart_empty)
    <ul class="woocommerce-mini-cart cart_list product_list_widget flex flex-col {{ e($list_class) }}">
        @php do_action('woocommerce_before_mini_cart_contents'); @endphp

        @foreach ($items as $item)
            <li
                class="woocommerce-mini-cart-item flex text-sm border-b last:border-b-0 border-dashed p-4 {{ e($item['class']) }}">
                <div class="flex gap-4">
                    @if (empty($item['permalink']))
                        <div class="w-16 h-16 flex-shrink-0 rounded-md overflow-hidden shadow">
                            {!! $item['thumb_html'] !!}
                        </div>
                    @else
                        <a href="{{ esc_url($item['permalink']) }}"
                            class="w-16 h-16 flex-shrink-0 rounded-md overflow-hidden shadow">
                            {!! $item['thumb_html'] !!}
                        </a>
                    @endif

                    <div class="min-w-0">
                        {{-- Clean title (no variation suffix) --}}
                        @if (empty($item['permalink']))
                            <p class="truncate">{!! $item['display_title'] !!}</p>
                        @else
                            <a href="{{ esc_url($item['permalink']) }}" class="no-underline! hover:underline! truncate">
                                {!! $item['display_title'] !!}
                            </a>
                        @endif

                        {{-- Attributes under title --}}
                        @if (!empty($item['attributes']))
                            <ul class="mt-1 text-xs text-muted-foreground space-y-0.5">
                                @foreach ($item['attributes'] as $attr)
                                    <li>
                                        <span class="font-medium">{{ $attr['key'] }}:</span>
                                        {!! $attr['value'] !!}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                {{-- Remove --}}
                <div class="ml-auto text-right whitespace-nowrap flex flex-col items-end gap-2">
                    {!! apply_filters(
                    'woocommerce_cart_item_remove_link',
                    '<a role="button" href="' . $item['remove']['url'] . '"'
                    . ' class="remove remove_from_cart_button text-muted-foreground hover:text-destructive [&_svg]:size-4"'
                    . ' aria-label="' . $item['remove']['aria_label'] . '"'
                    . ' data-product_id="' . $item['remove']['product_id'] . '"'
                    . ' data-cart_item_key="' . $item['remove']['cart_item_key'] . '"'
                    . ' data-product_sku="' . $item['remove']['sku'] . '"'
                    . ' data-success_message="' . $item['remove']['success_message'] . '">'
                    . (string) svg('lucide-x')->toHtml()
                    . '</a>',
                    $item['key']
                ) !!}

                    <div>
                        @isset($item['subtotal_html'])
                            <p class="text-sm font-medium">
                                {!! $item['subtotal_html'] !!}
                            </p>
                        @endisset

                        {{-- qty × price --}}
                        @if($item['qty'] > 1)
                            <p class="text-xs text-muted-foreground">
                                {!! sprintf('%s &times; %s', $item['qty'], $item['price_html']) !!}
                            </p>
                        @endif
                    </div>

                </div>
            </li>
        @endforeach

        @php do_action('woocommerce_mini_cart_contents'); @endphp
    </ul>

    <div class="bg-sidebar border-t p-4 mt-auto sticky bottom-0 flex flex-col gap-6 pt-6">
        <p class="woocommerce-mini-cart__total total w-full text-right">
            @php do_action('woocommerce_widget_shopping_cart_total'); @endphp
        </p>

        @php do_action('woocommerce_widget_shopping_cart_before_buttons'); @endphp
        <p class="woocommerce-mini-cart__buttons buttons flex gap-2 w-full">
            @php do_action('woocommerce_widget_shopping_cart_buttons'); @endphp
        </p>
        @php do_action('woocommerce_widget_shopping_cart_after_buttons'); @endphp
    </div>
@else
    <p class="woocommerce-mini-cart__empty-message">{{ esc_html__('No products in the cart.', 'woocommerce') }}</p>
@endif

@php do_action('woocommerce_after_mini_cart'); @endphp