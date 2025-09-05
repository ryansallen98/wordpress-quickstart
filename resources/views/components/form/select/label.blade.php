<span
  data-slot="select-label"
  class="text-muted-foreground px-2 py-1.5 text-xs"
  x-show="!query || !query.trim().length" 
>
  {{ $slot }}
</span>