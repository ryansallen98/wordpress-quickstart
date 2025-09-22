@php
    global $product;

    if (!$product->is_purchasable()) {
        return;
    }
@endphp

<div class="mt-4 mb-8">
    <div>
        {!! wc_get_stock_html($product) !!}
    </div>

    @if ($product->is_in_stock())

        @php
            do_action('woocommerce_before_add_to_cart_form');
        @endphp

        <form class="cart"
            action="{!! esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())) !!}"
            method="post" enctype='multipart/form-data'>
            @php
                do_action('woocommerce_before_add_to_cart_button');
            @endphp

            @php woocommerce_upsell_display(); @endphp

            <div class="flex justify-between items-end">
                <div class="flex flex-col gap-2">
                    <label class="input-label">Quantity</label>
                    @php
                        do_action('woocommerce_before_add_to_cart_quantity');

                        woocommerce_quantity_input(
                            array(
                                'min_value' => apply_filters('woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product),
                                'max_value' => apply_filters('woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product),
                                'input_value' => isset($_POST['quantity']) ? wc_stock_amount(wp_unslash($_POST['quantity'])) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
                            )
                        );

                        do_action('woocommerce_after_add_to_cart_quantity');
                    @endphp
                </div>
                <button type="submit" name="add-to-cart" value="{!! esc_attr($product->get_id()) !!}"
                    class="single_add_to_cart_button button alt{!! esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : '') !!}">
                    <x-heroicon-s-shopping-bag aria-hidden="true" />
                    {!! esc_html($product->single_add_to_cart_text()) !!}
                </button>

            </div>

            @php
                do_action('woocommerce_after_add_to_cart_button');
            @endphp

        </form>

        @php
            do_action('woocommerce_after_add_to_cart_form'); 
        @endphp

    @endif
</div>