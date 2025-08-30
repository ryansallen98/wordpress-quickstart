@props(['type' => 'single'])

<div
  {{ $attributes }}
  role="presentation"
  x-data="accordionComponent({ type: @js($type) })"
>
  {{ $slot }}
</div>