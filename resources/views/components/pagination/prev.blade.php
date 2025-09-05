@props([
  'href' => '#'
])

<x-button
  aria-label="{{ __('Go to previous page', 'wordpress-quickstart') }}"
  variant="outline"
  size="default"
  href="{{ $href }}"
>
  <x-lucide-chevron-left aria-hidden="true" />
  <span class="hidden sm:block">
    {{ __('Previous', 'wordpress-quickstart') }}
  </span>
</x-button>
