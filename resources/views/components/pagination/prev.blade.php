@props([
  'href' => '#'
])

<a
  aria-label="{{ __('Go to previous page', 'wordpress-quickstart') }}"
  class="btn btn-outline"
  href="{{ $href }}"
>
  <x-lucide-chevron-left aria-hidden="true" />
  <span class="hidden sm:block">
    {{ __('Previous', 'wordpress-quickstart') }}
  </span>
</a>
