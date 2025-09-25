@php
  global $product;

  $image = $product->get_image(
    'woocommerce_thumbnail',
    ['class' => 'group-hover/product-link:scale-105 transition-transform duration-200 ease-in-out mb-0! w-full aspect-square min-w-36'], // add your classes here
  );
@endphp

<div class="overflow-hidden z-1 w-full rounded-lg shadow-md bg-gradient-to-t from-stone-100 dark:from-accent to-bg-background dark:to-accent/50">
  {!! $image !!}
</div>
