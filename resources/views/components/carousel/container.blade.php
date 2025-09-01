@php
  // Compose classes using TailwindMerge
  $composed = $tw->merge(
    'flex flex-row data-[orientation=vertical]:flex-col',
    $attributes->get('class'),
  );
@endphp

<div
  class="overflow-hidden"
  x-ref="viewport"
  role="group"
  x-bind:aria-roledescription="@js(__('slide container', 'wordpress-quickstart'))"
>
  <div
    {{ $attributes->merge(['class' => $composed]) }}
    x-bind:data-orientation="isVertical ? 'vertical' : 'horizontal'"
  >
    {{ $slot }}
  </div>
</div>
