@php
  global $post, $product;
@endphp

@if ($product && $product->is_on_sale())
  <span
    class="bg-red-600 dark:bg-red-600 text-white absolute flex px-8 py-1 justify-center shadow-lg items-center rotate-45 top-2 right-[-32px] text-sm font-bold uppercase z-2"
  >
    {{ esc_html__('Sale!', 'woocommerce') }}
  </span>
@endif
