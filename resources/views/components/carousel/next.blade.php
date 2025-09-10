@props([
  'variant' => 'outline',
  'size' => 'icon',
])

@php
  $composed = $tw->merge('absolute size-8 rounded-full top-1/2 right-4 -translate-y-1/2', $attributes->get('class'));
@endphp

@php
  $composed = $tw->merge(
    '
      absolute size-8 rounded-full 
      data-[orientation=horizontal]:top-1/2 
      data-[orientation=horizontal]:right-4 data-[orientation=horizontal]:-translate-y-1/2 
      data-[orientation=vertical]:bottom-4 data-[orientation=vertical]:left-1/2 
      data-[orientation=vertical]:-translate-x-1/2 data-[orientation=vertical]:rotate-90',
    $attributes->get('class'),
  );
@endphp

<button
  {{ $attributes->merge(['class' => $composed]) }}
  type="button"
  x-on:click="next"
  x-on:keydown.right.prevent.stop="if (!isVertical) next()"
  x-on:keydown.down.prevent.stop="if (isVertical) next()"
  x-bind:disabled="!canNext"
  x-bind:aria-disabled="!canNext ? 'true' : 'false'"
  x-bind:aria-controls="$id('viewport')"
  x-bind:aria-keyshortcuts="isVertical ? 'ArrowDown' : 'ArrowRight'"
  x-bind:data-orientation="isVertical ? 'vertical' : 'horizontal'"
  size="{{ $size }}"
  variant="{{ $variant }}"
>
  <x-lucide-chevron-right aria-hidden="true" />
  <span class="sr-only">{{ __('Next slide', 'wordpress-quickstart') }}</span>
</button>