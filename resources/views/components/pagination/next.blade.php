@props([
  'href' => '#'
])

<x-button
  aria-label="{{ __('Go to next page', 'wordpress-quickstart') }}"
  variant="outline"
  size="default"
  href="{{ $href }}"
>
  <span class="hidden sm:block">{{ __('Next', 'wordpress-quickstart') }}</span>
  <x-lucide-chevron-right aria-hidden="true" />
</x-button>
