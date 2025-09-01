@php
  $classes = $tw->merge(
    "row-start-3 col-[2/-1] flex items-center pt-2",
    $attributes->get('class'),
  );
@endphp

<div data-slot="alert-actions" {{ $attributes->merge(['class' => $classes]) }}>
  {{ $slot }}
</div>