@php
  // Compose classes using TailwindMerge
  $composed = $tw->merge(
    'text-muted-foreground col-start-2 grid justify-items-start gap-1 text-sm [&_p]:leading-relaxed',
    $attributes->get('class'),
  );
@endphp

<div
  {{ $attributes->merge(['class' => $composed]) }}
  data-slot="alert-description"
>
  {{ $slot }}
</div>
