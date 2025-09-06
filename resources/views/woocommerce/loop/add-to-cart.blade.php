{{--
  Loop Add to Cart
  
  This template can be overridden by copying it to yourtheme/woocommerce/loop/add-to-cart.php.
  
  HOWEVER, on occasion WooCommerce will need to update template files and you
  (the theme developer) will need to copy the new files to your theme to
  maintain compatibility. We try to do this as little as possible, but it does
  happen. When this occurs the version of the template file will be bumped and
  the readme will list any important changes.
  
  @see         https://woocommerce.com/document/template-structure/
  @package     WooCommerce\Templates
  @version     9.2.0
--}}

@php
  $aria_describedby = isset($args['aria-describedby_text'])
    ? sprintf('aria-describedby="woocommerce_loop_add_to_cart_link_describedby_%s"', esc_attr($id))
    : '';

  $attrBag = new \Illuminate\View\ComponentAttributeBag(
    collect($attributes)
      ->mapWithKeys(fn ($v, $k) => [$k => $v])
      ->all(),
  );
@endphp

<a
  class="btn btn-outline"
  href="{{ $href }}"
  target="{{ $target }}"
  aria-describedby="{{ $aria_describedby }}"
  data-quantity="{{ esc_attr($args['quantity'] ?? 1) }}"
  {{ $attrBag }}
>
  @switch($type)
    @case('simple')
      <x-lucide-shopping-cart aria-hidden="true" />

      @break
    @case('variable')
      <x-lucide-circle-ellipsis aria-hidden="true" />

      @break
    @case('grouped')
      <x-lucide-package-open aria-hidden="true" />

      @break
    @case('external')
      <x-lucide-square-arrow-out-up-right aria-hidden="true" />

      @break
  @endswitch
  {{ $label }}
</a>

@if (! empty($args['aria-describedby_text'] ?? null))
  <span
    id="woocommerce_loop_add_to_cart_link_describedby_{{ esc_attr($id) }}"
    class="sr-only"
  >
    {{ esc_html($args['aria-describedby_text']) }}
  </span>
@endif
