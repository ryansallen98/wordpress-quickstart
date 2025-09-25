<div class="grid grid-cols-[3fr_1fr] gap-8">
    <form class="woocommerce-cart-form w-full mt-8" action="{{ esc_url(wc_get_cart_url()) }}" method="post">
        @php do_action('woocommerce_before_cart_table') @endphp

        <div class="overflow-x-auto rounded-lg shadow border">
            <table class="min-w-full divide-y text-sm">
                <thead class="thead">
                    <tr>
                        <th class="product-remove th"><span
                                class="sr-only">{{ __('Remove item', 'woocommerce') }}</span>
                        </th>
                        <th class="product-thumbnail th"><span
                                class="sr-only">{{ __('Thumbnail image', 'woocommerce') }}</span></th>
                        <th scope="col" class="product-name th">{{ __('Product', 'woocommerce') }}</th>
                        <th scope="col" class="product-price th text-right">{{ __('Price', 'woocommerce') }}</th>
                        <th scope="col" class="product-quantity th">{{ __('Quantity', 'woocommerce') }}</th>
                        <th scope="col" class="product-subtotal th text-right">{{ __('Subtotal', 'woocommerce') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php do_action('woocommerce_before_cart_contents') @endphp

                    @foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item)
                        @php
                            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                            $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
                            $product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
                        @endphp

                        @if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key))
                                        @php
                                            $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                                        @endphp
                                        <tr
                                            class="woocommerce-cart-form__cart-item {{ esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)) }}">
                                            <td class="product-remove td">
                                                {!! apply_filters(
                                'woocommerce_cart_item_remove_link',
                                sprintf(
                                    '<a role="button" href="%s" class="remove btn btn-ghost btn-icon" aria-label="%s" data-product_id="%s" data-product_sku="%s">%s</a>',
                                    esc_url(wc_get_cart_remove_url($cart_item_key)),
                                    esc_attr(sprintf(__('Remove %s from cart', 'woocommerce'), wp_strip_all_tags($product_name))),
                                    esc_attr($product_id),
                                    esc_attr($_product->get_sku()),
                                    (string) svg('lucide-x')->toHtml()
                                ),
                                $cart_item_key
                            ) !!}
                                            </td>

                                            <td class="product-thumbnail td">
                                                @php
                                                    $thumbnail = apply_filters(
                                                        'woocommerce_cart_item_thumbnail',
                                                        $_product->get_image(['60', '60'], ['class' => 'rounded-md shadow-md']),
                                                        $cart_item,
                                                        $cart_item_key
                                                    );
                                                @endphp
                                                @if (!$product_permalink)
                                                    {!! $thumbnail !!}
                                                @else
                                                    <a href="{{ esc_url($product_permalink) }}" class="block">{!! $thumbnail !!}</a>
                                                @endif
                                            </td>

                                            <td scope="row" role="rowheader" class="product-name td"
                                                data-title="{{ esc_attr__('Product', 'woocommerce') }}">
                                                @if (!$product_permalink)
                                                    {!! wp_kses_post($product_name . '&nbsp;') !!}
                                                @else
                                                    {!! wp_kses_post(apply_filters('woocommerce_cart_item_name', sprintf('<a href="%s" class="no-underline! hover:underline!">%s</a>', esc_url($product_permalink), $_product->get_name()), $cart_item, $cart_item_key)) !!}
                                                @endif

                                                @php do_action('woocommerce_after_cart_item_name', $cart_item, $cart_item_key) @endphp

                                                {!! wc_get_formatted_cart_item_data($cart_item) !!}

                                                @if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity']))
                                                    {!! wp_kses_post(apply_filters('woocommerce_cart_item_backorder_notification', '<p class="backorder_notification text-xs text-yellow-600 mt-1">' . esc_html__('Available on backorder', 'woocommerce') . '</p>', $product_id)) !!}
                                                @endif
                                            </td>

                                            <td class="product-price td text-right" data-title="{{ esc_attr__('Price', 'woocommerce') }}">
                                                {!! apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key) !!}
                                            </td>

                                            <td class="product-quantity td" data-title="{{ esc_attr__('Quantity', 'woocommerce') }}">
                                                <div class="flex items-center justify-center mx-auto">
                                                    @php
                                                        if ($_product->is_sold_individually()) {
                                                            $min_quantity = 1;
                                                            $max_quantity = 1;
                                                        } else {
                                                            $min_quantity = 0;
                                                            $max_quantity = $_product->get_max_purchase_quantity();
                                                        }

                                                        $product_quantity = woocommerce_quantity_input([
                                                            'input_name' => "cart[{$cart_item_key}][qty]",
                                                            'input_value' => $cart_item['quantity'],
                                                            'max_value' => $max_quantity,
                                                            'min_value' => $min_quantity,
                                                            'product_name' => $product_name,
                                                        ], $_product, false);
                                                    @endphp
                                                </div>
                                                {!! apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item) !!}
                                            </td>

                                            <td class="product-subtotal td text-right" data-title="{{ esc_attr__('Subtotal', 'woocommerce') }}">
                                                {!! apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key) !!}
                                            </td>
                                        </tr>
                        @endif
                    @endforeach

                    @php do_action('woocommerce_cart_contents') @endphp

                    <tr>
                        <td colspan="6" class="actions td border-b-0!">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                @if (wc_coupons_enabled())
                                    <div class="coupon flex items-center gap-2">
                                        <label for="coupon_code" class="sr-only">{{ __('Coupon:', 'woocommerce') }}</label>
                                        <input type="text" name="coupon_code" class="input-text" id="coupon_code" value=""
                                            placeholder="{{ esc_attr__('Coupon code', 'woocommerce') }}" />
                                        <button type="submit" class="btn btn-outline" name="apply_coupon"
                                            value="{{ esc_attr__('Apply coupon', 'woocommerce') }}">
                                            <x-lucide-ticket-percent />
                                            {{ __('Apply coupon', 'woocommerce') }}</button>
                                        @php do_action('woocommerce_cart_coupon') @endphp
                                    </div>
                                @endif

                                <div class="flex gap-2">
                                    <button type="submit" class="btn btn-primary" name="update_cart"
                                        value="{{ esc_attr__('Update cart', 'woocommerce') }}">
                                        <x-lucide-refresh-ccw />
                                        {{ __('Update cart', 'woocommerce') }}</button>
                                    @php do_action('woocommerce_cart_actions') @endphp
                                </div>
                            </div>
                            {!! wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce', true, false) !!}
                        </td>
                    </tr>

                    @php do_action('woocommerce_after_cart_contents') @endphp
                </tbody>
            </table>
        </div>
        @php do_action('woocommerce_after_cart_table') @endphp
    </form>

    <div class="cart-collaterals mt-8 max-w-4xl mx-auto">
        @php do_action('woocommerce_cart_collaterals') @endphp
    </div>
</div>