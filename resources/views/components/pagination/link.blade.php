@props([
  'isActive' => false,
  'href' => '#'
])

<x-button
  aria-current="{{ $isActive ? 'page' : 'false' }}"
  data-slot="pagination-link"
  data-active="{{ $isActive ? 'true' : 'false' }}"
  variant="{{ $isActive ? 'outline' : 'ghost' }}"
  size="icon"
  href="{{ $href }}"
>
  {!! $slot !!}
</x-button>
