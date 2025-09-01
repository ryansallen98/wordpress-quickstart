@php
  // Compose classes using TailwindMerge
  $composed = $tw->merge(
    'col-start-2 line-clamp-1 min-h-4 font-medium tracking-tight',
    $attributes->get('class'),
  );
@endphp

<div
  {{ $attributes->merge(['class' => $composed]) }}
  data-slot="alert-title"
>
  {{ $slot }}
</div>
