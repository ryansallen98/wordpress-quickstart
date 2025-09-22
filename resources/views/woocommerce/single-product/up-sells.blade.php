@php
  global $upsell_product;
  $current_product = $upsell_product instanceof WC_Product ? $upsell_product : wc_get_product(get_the_ID());

  // Base price (tax/display aware)
  $base_price = $current_product ? wc_get_price_to_display($current_product) : 0.0;

  $products = collect($upsells ?? [])->map(fn($item) =>
      $item instanceof WC_Product ? $item : wc_get_product($item)
  )->filter();

  $heading = apply_filters('woocommerce_product_upsells_products_heading', __('Upgrade your order!', 'woocommerce'));
@endphp

@if($products->isNotEmpty())
  <div class="flex flex-col gap-3 my-4 up-sells upsells products">
    @if($heading)
      <h2 class="text-lg font-semibold">{!! esc_html($heading) !!}</h2>
    @endif

    <div class="border-2 border-dashed border-primary p-4 rounded-md bg-primary/10 shadow-sm">
      @foreach($products as $upsell_product)
        @php
          $id         = $upsell_product->get_id();
          $name       = $upsell_product->get_name();
          $permalink  = get_permalink($id);
          $img_id     = $upsell_product->get_image_id();
          $img_src    = $img_id ? wp_get_attachment_image_url($img_id, 'woocommerce_thumbnail') : wc_placeholder_img_src('woocommerce_thumbnail');
          $desc       = $upsell_product->get_short_description() ?: wp_strip_all_tags(get_the_excerpt($id));

          // Prices (numeric, tax/display aware)
          $upsell_price_num = wc_get_price_to_display($upsell_product);
          $diff_raw         = $upsell_price_num - (float) $base_price;

          // Label text
          $label_html = $diff_raw > 0
              ? sprintf(__('Upgrade for %s','your-td'), wc_price($diff_raw))
              : sprintf(__('Purchase for %s','your-td'), wc_price($upsell_price_num));

          // Button + URL
          $supports_ajax   = $upsell_product->supports('ajax_add_to_cart');
          $btn_classes     = trim('btn btn-sm btn-outline add_to_cart_button ' . ($supports_ajax ? 'ajax_add_to_cart' : ''));
          $add_to_cart_url = $upsell_product->add_to_cart_url();
          $aria            = esc_attr($upsell_product->add_to_cart_description());
        @endphp

        <div class="flex gap-4 items-center py-3 first:pt-0 last:pb-0 border-b border-primary border-dashed last:border-b-0">
          <a href="{{ $permalink }}" class="shrink-0">
            <img src="{{ esc_url($img_src) }}" alt="{{ esc_attr($name) }}" class="w-30 h-30 object-cover rounded-md aspect-square" />
          </a>

          <div class="min-w-0">
            <a href="{{ $permalink }}" class="text-md font-medium line-clamp-2 no-underline!">{{ $name }}</a>

            @if($desc)
              <p class="text-xs text-muted-foreground mt-1 line-clamp-2">{{ wp_strip_all_tags($desc) }}</p>
            @endif

            <div class="mt-2">
              <a href="{{ esc_url($add_to_cart_url) }}"
                 data-quantity="1"
                 data-product_id="{{ $id }}"
                 data-product_sku="{{ esc_attr($upsell_product->get_sku()) }}"
                 class="{{ $btn_classes }}"
                 aria-label="{{ $aria }}"
                 rel="nofollow">
                 <x-lucide-circle-fading-plus aria-hidden="true" />
                 {!! $label_html !!}
              </a>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
@endif

@php wp_reset_postdata(); @endphp