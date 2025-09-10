@props([
  'variant' => 'outline',
  'size' => 'icon',
])

@php
  $composed = $tw->merge(
    '
      absolute size-8 rounded-full 
      data-[orientation=horizontal]:top-1/2 
      data-[orientation=horizontal]:left-4 data-[orientation=horizontal]:-translate-y-1/2 
      data-[orientation=vertical]:top-4 data-[orientation=vertical]:left-1/2 
      data-[orientation=vertical]:-translate-x-1/2 data-[orientation=vertical]:rotate-90',
    $attributes->get('class'),
  );
@endphp

<button
  {{ $attributes->merge(['class' => $composed]) }}
  type="button"
  x-on:click="prev"
  x-on:keydown.left.prevent.stop="if (!isVertical) prev()"
  x-on:keydown.up.prevent.stop="if (isVertical) prev()"
  x-bind:disabled="!canPrev"
  x-bind:aria-disabled="!canPrev ? 'true' : 'false'"
  x-bind:aria-controls="$id('viewport')"
  x-bind:aria-keyshortcuts="isVertical ? 'ArrowUp' : 'ArrowLeft'"
  x-bind:data-orientation="isVertical ? 'vertical' : 'horizontal'"
  size="{{ $size }}"
  variant="{{ $variant }}"
>
  <x-lucide-chevron-left aria-hidden="true" />
  <span class="sr-only">
    {{ __('Previous slide', 'wordpress-quickstart') }}
  </span>
</button>
