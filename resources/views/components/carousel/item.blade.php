@props([
  'index' => null,
])

@php
  // Compose classes using TailwindMerge
  $composed = $tw->merge(
    'shrink-0 basis-full',
    $attributes->get('class'),
  );
@endphp

<div
  {{ $attributes->merge(['class' => $composed]) }}
  role="group"
  aria-roledescription="{{ __('slide', 'wordpress-quickstart') }}"
  @if(!is_null($index))
    data-index="{{ (int) $index }}"
  @endif
  x-bind:aria-label="
    (() => {
      const current =
        $el.dataset.index !== undefined
          ? Number($el.dataset.index) + 1
          : Array.from($el.parentNode.children).indexOf($el) + 1;

      return (@js(__('Slide :current of :total', 'wordpress-quickstart')))
        .replace(':current', current)
        .replace(':total', slideCount);
    })()
  "
>
  {{ $slot }}
</div>