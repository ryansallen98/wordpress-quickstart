@props([
  'index' => null,
  'lazy' => false,
])

@php
  // Compose classes using TailwindMerge
  $composed = $tw->merge(
    'shrink-0 basis-full',
    $attributes->get('class'),
    $lazy ? 'relative' : ''
  );
@endphp

<div
  {{ $attributes->merge(['class' => $composed]) }}
  role="group"
  aria-roledescription="{{ __('slide', 'wordpress-quickstart') }}"
  @if(!is_null($index))
    data-index="{{ (int) $index }}"
  @endif
  :data-active="selectedIndex === (
    $el.dataset.index !== undefined
      ? Number($el.dataset.index)
      : Array.from($el.parentNode.children).indexOf($el)
  ) ? 'true' : 'false'"
  :data-sibling-active="(selectedIndex === (
    $el.dataset.index !== undefined
      ? Number($el.dataset.index)
      : Array.from($el.parentNode.children).indexOf($el)
  ) - 1 || (selectedIndex === 0 && slideCount - 1)) ? 'true' : 'false'"
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
  @if ($lazy)
  <div class="lazy-load__spinner absolute inset-0 grid place-items-center bg-accent">
      <span class="sr-only"> {{ __('Loading', 'wordpress-quickstart') }}</span>
      <x-lucide-loader-circle class="animate-spin size-8 text-primary" aria-hidden="true"/>
  </div>
  @endif

  {{ $slot }}
</div>