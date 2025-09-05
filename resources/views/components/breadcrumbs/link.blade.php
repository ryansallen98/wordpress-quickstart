@props(['href' => '#'])

<a data-slot="breadcrumb-link" class="hover:text-foreground transition-colors no-underline!" href="{{ $href }}">
  {{ $slot }}
</a>
