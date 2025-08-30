@props(['open' => false])

@php
  $base = 'border-b last:border-b-0';
  $class = $tw->merge($base, $attributes->get('class'));
@endphp

<div
  {{ $attributes->except('class') }}
  role="group"
  class="{{ $class }}"
  x-id="['acc']"
  @if($open) x-init="toggle($id('acc'))" @endif
>
  {{ $slot }}
</div>