<nav
  aria-label="{{ __('Breadcrumbs', 'wordpress-quickstart') }}"
  data-slot="breadcrumbs"
>
  <ol
    data-slot="breadcrumb-list"
    class="text-muted-foreground flex flex-wrap items-center gap-1.5 text-sm break-words"
  >
    {{ $slot }}
  </ol>
</nav>
