@php
  $base = 'overflow-hidden text-sm';
  $class = $tw->merge($base, $attributes->get('class'));
@endphp

<div
  {{ $attributes->except('class') }}
  :id="`panel_${$id('acc')}`"
  role="region"
  :aria-labelledby="`acc_${$id('acc')}`"
  x-cloak
  x-show="isOpen($id('acc'))"
  x-collapse.duration.200ms
  x-bind:style="
    window.matchMedia('(prefers-reduced-motion: reduce)').matches
      ? 'transition-duration:0ms'
      : ''
  "
  :data-state="isOpen($id('acc')) ? 'open' : 'closed'"
  class="{{ $class }}"
  :aria-hidden="!isOpen($id('acc'))"
>
  <div class="pt-0 pb-4">
    {{ $slot }}
  </div>
</div>
