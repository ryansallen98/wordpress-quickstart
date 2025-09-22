@props([
  'orientation' => 'horizontal', // 'horizontal' | 'vertical'
  'dir' => 'ltr',                // 'ltr' | 'rtl'
  // Accept boolean or an assoc array with any Autoplay option:
  // ['delay' => 5000, 'jump' => false, 'playOnInit' => true, 'stopOnInteraction' => true,
  //  'stopOnMouseEnter' => true, 'stopOnFocusIn' => true, 'stopOnLastSnap' => false]
  'autoplay' => false,
  'lazy' => false,
  'draggable' => true,
  'align' => 'start', 
])

@php
  $isVertical = strtolower($orientation) === 'vertical';
  $composed = $tw->merge('relative', $attributes->get('class'));
@endphp

<div
  {{ $attributes->merge(['class' => $composed]) }}
  x-data="carouselComponent({
    loop: true,
    align: '{{ $align }}',
    slidesToScroll: 1,
    axis: '{{ $isVertical ? 'y' : 'x' }}',
    direction: '{{ $dir === 'rtl' ? 'rtl' : 'ltr' }}',
    autoplay: @js($autoplay),
    draggable: @js((bool) $draggable),
    lazy: @js($lazy), 
  })"
  x-init
  x-effect="() => () => destroy()"
  x-id="['viewport', 'carousel', 'slide']"
  role="region"
  aria-roledescription="{{ __('carousel', 'wordpress-quickstart') }}"
  x-bind:aria-labelledby="$id('carousel')"
  dir="{{ $dir === 'rtl' ? 'rtl' : 'ltr' }}"
  tabindex="0"
  x-on:focusin="hasFocusWithin = true"
  x-on:focusout="hasFocusWithin = $el.contains($event.relatedTarget)"
>
  <h2 :id="$id('carousel')" class="sr-only">
    {{ __('Carousel', 'wordpress-quickstart') }}
  </h2>

  {{ $slot }}

  <p
    class="sr-only"
    aria-live="polite"
    x-text="
      (@js(__('Slide :current of :total', 'wordpress-quickstart')))
        .replace(':current', (selectedIndex + 1))
        .replace(':total', slideCount)
    "
  ></p>
</div>