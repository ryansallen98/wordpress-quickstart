@props([
  'href' => '#'
])

<a
  aria-label="{{ __('Go to next page', 'wordpress-quickstart') }}"
  class="btn btn-outline"
  href="{{ $href }}"
>
  <span class="hidden sm:block">{{ __('Next', 'wordpress-quickstart') }}</span>
  <x-lucide-chevron-right aria-hidden="true" />
</a>
