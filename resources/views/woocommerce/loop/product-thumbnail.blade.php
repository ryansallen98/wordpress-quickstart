@php
  global $product;

  $image = $product->get_image(
    'woocommerce_thumbnail',
    ['class' => 'group-hover/product-link:scale-105 transition-transform duration-200 ease-in-out mb-0! w-full aspect-square'], // add your classes here
  );
@endphp

<div class="overflow-hidden z-1 w-full">
  {!! $image !!}
</div>
