@props([
  'isActive' => false,
  'href' => '#'
])

<a
  aria-current="{{ $isActive ? 'page' : 'false' }}"
  data-slot="pagination-link"
  data-active="{{ $isActive ? 'true' : 'false' }}"
  href="{{ $href }}"
  class="btn {{ $isActive ? 'btn-outline' : 'btn-ghost' }} btn-icon"
>
  {!! $slot !!}
</a>
