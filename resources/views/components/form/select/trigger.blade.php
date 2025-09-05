@props(['disabled' => false])

@php
  $classes = $tw->merge(
    "
        border-input data-[placeholder]:text-muted-foreground [&_svg:not([class*='text-'])]:text-muted-foreground 
        focus-visible:border-ring focus-visible:ring-ring/50 
        aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive 
        dark:bg-input/30 dark:hover:bg-input/50 
        flex w-fit min-w-[180px] items-center justify-between gap-2 
        rounded-md border bg-transparent px-3 py-2 text-sm whitespace-nowrap shadow-xs 
        transition-[color,box-shadow] outline-none focus-visible:ring-[3px] 
        disabled:cursor-not-allowed disabled:opacity-50 
        data-[size=default]:h-9 data-[size=sm]:h-8 
        *:data-[slot=select-value]:line-clamp-1 *:data-[slot=select-value]:flex *:data-[slot=select-value]:items-center 
        *:data-[slot=select-value]:gap-2 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4
        data-[state=open]:[&_svg]:rotate-180 [&_svg]:transition-transform [&_svg]:duration-200 [&_svg]:ease-in-out
      ",
    $attributes->get('class'),
  );
@endphp

<button
  type="button"
  {{ $attributes->merge(['class' => $classes]) }}
  x-ref="trigger"
  :aria-expanded="open.toString()"
  aria-haspopup="listbox"
  :aria-controls="$id('listbox')"
  :data-state="open ? 'open' : 'closed'"
  :id="triggerId"
  @click="toggleOpen()"
  @keydown.arrow-down.prevent.stop="openMenu(); moveActive(1)"
  @keydown.arrow-up.prevent.stop="openMenu(); moveActive(-1)"
  @keydown.enter.prevent.stop="open ? selectActive() : openMenu()"
  @keydown.space.prevent.stop="open ? selectActive() : openMenu()"
  :disabled="@js($disabled)"
>
  {{ $slot }}
  <x-lucide-chevron-down aria-hidden="true" />
</button>
