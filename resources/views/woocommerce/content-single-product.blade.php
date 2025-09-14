@php
    global $product;

    // Build classes for the wrapper the Woo way.
    $product_classes = function_exists('wc_get_product_class')
        ? wc_get_product_class('', $product) // returns array of classes
        : [];
@endphp

{{-- Before single product (notices, etc.) --}}
@php do_action('woocommerce_before_single_product'); @endphp

@if (post_password_required())
    {!! get_the_password_form() !!}
    @php return; @endphp
@endif

<div id="product-{{ get_the_ID() }}" class="{{ esc_attr(implode(' ', (array) $product_classes)) }}">

    <div class="grid grid-cols-[3fr_4fr] gap-16 mt-4">
        
        {{-- Before summary (sale flash, gallery) --}}
        <div class="relative">
            @php
                // @hooked woocommerce_show_product_sale_flash - 10
                // @hooked woocommerce_show_product_images - 20
                do_action('woocommerce_before_single_product_summary');
              @endphp
        </div>

        <div class="summary entry-summary">
            @php
                // @hooked woocommerce_template_single_title - 5
                // @hooked woocommerce_template_single_rating - 10
                // @hooked woocommerce_template_single_price - 10
                // @hooked woocommerce_template_single_excerpt - 20
                // @hooked woocommerce_template_single_add_to_cart - 30
                // @hooked woocommerce_template_single_meta - 40
                // @hooked woocommerce_template_single_sharing - 50
                // @hooked WC_Structured_Data::generate_product_data() - 60
                do_action('woocommerce_single_product_summary');
            @endphp
        </div>
    </div>

    @php
        // After summary (tabs, upsells, related)
        // @hooked woocommerce_output_product_data_tabs - 10
        // @hooked woocommerce_upsell_display - 15
        // @hooked woocommerce_output_related_products - 20
        do_action('woocommerce_after_single_product_summary');
      @endphp
</div>

@php do_action('woocommerce_after_single_product'); @endphp