@if ($related_products)
  <section class="related products">
    @php
      $heading = apply_filters(
        'woocommerce_product_related_products_heading',
        __('Related products', 'woocommerce')
      );
    @endphp

    @if ($heading)
      <h2 class="text-2xl font-bold mb-4">{{ $heading }}</h2>
    @endif

    <div class="grid grid-cols-2 md:grid-cols-2 gap-6">
      @foreach ($related_products as $related_product)
        @php
          $post_object = get_post($related_product->get_id());
          setup_postdata($GLOBALS['post'] = $post_object);
        @endphp

        {{-- Reuse your product card partial --}}
        {!! wc_get_template_part('content', 'product') !!}
      @endforeach
    </div>
  </section>

  @php wp_reset_postdata(); @endphp
@endif