@php
    defined('ABSPATH') || exit;

    // Ensure $product is available
    if (!isset($product) || !$product) {
        global $product;
        if (!$product) {
            $product = wc_get_product(get_the_ID());
        }
    }

    // Build button class with theme support
    $theme_btn = wc_wp_theme_get_element_class_name('button');
    $btn_class = 'single_add_to_cart_button button alt' . ($theme_btn ? ' ' . $theme_btn : '');

    // Quantity input args (match core)
    $qty_args = [
        'min_value' => apply_filters('woocommerce_quantity_input_min', $product ? $product->get_min_purchase_quantity() : 1, $product),
        'max_value' => apply_filters('woocommerce_quantity_input_max', $product ? $product->get_max_purchase_quantity() : -1, $product),
        'input_value' => isset($_POST['quantity'])
            ? wc_stock_amount(wp_unslash($_POST['quantity']))
            : ($product ? $product->get_min_purchase_quantity() : 1),
    ];
@endphp

<div class="woocommerce-variation-add-to-cart variations_button">

    @php do_action('woocommerce_before_add_to_cart_button'); @endphp

    <div class="woocommerce-variation single_variation text-right text-2xl font-semibold [&_.price]:inline-flex [&_.price]:mt-4" role="alert"
        aria-relevant="additions">
    </div>

    <div class="flex justify-between items-end">
        <div class="flex flex-col gap-2">
            <label class="input-label">Quantity</label>
            @php do_action('woocommerce_before_add_to_cart_quantity'); @endphp

            @php woocommerce_quantity_input($qty_args); @endphp

            @php do_action('woocommerce_after_add_to_cart_quantity'); @endphp
        </div>

        <button type="submit" class="btn btn-primary btn-lg {{ esc_attr($btn_class) }}">
            <x-heroicon-s-shopping-bag aria-hidden="true" />
            {{ esc_html($product ? $product->single_add_to_cart_text() : __('Add to cart', 'woocommerce')) }}
        </button>
    </div>

    @php do_action('woocommerce_after_add_to_cart_button'); @endphp

    <input type="hidden" name="add-to-cart" value="{{ esc_attr($product ? $product->get_id() : 0) }}" />
    <input type="hidden" name="product_id" value="{{ esc_attr($product ? $product->get_id() : 0) }}" />
    <input type="hidden" name="variation_id" class="variation_id" value="0" />
</div>