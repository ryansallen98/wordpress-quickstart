@php
    if (!defined('ABSPATH')) {
        exit;
    }
@endphp

@php do_action('woocommerce_before_add_to_cart_form'); @endphp

<form class="cart mt-4 mb-4" action="{!! esc_url($product_url) !!}" method="get">
    @php do_action('woocommerce_before_add_to_cart_button'); @endphp

    @php woocommerce_upsell_display(); @endphp

    <button type="submit"
        class="single_add_to_cart_button button alt{!! esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : '') !!}">

        <x-lucide-square-arrow-out-up-right aria-hidden="true" />
        {!! esc_html($button_text) !!}
    </button>

    @php wc_query_string_form_fields($product_url); @endphp

    @php do_action('woocommerce_after_add_to_cart_button'); @endphp
</form>

@php do_action('woocommerce_after_add_to_cart_form'); @endphp