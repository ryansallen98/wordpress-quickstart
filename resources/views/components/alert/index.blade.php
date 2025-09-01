@props([
  'variant' => 'default',
  'closable' => false,
  'timeout' => null,
  'hasActions' => false,
])

@php
  $base = "relative w-full rounded-lg border px-4 py-3 text-sm grid
           /* rows */
           grid-rows-[auto]
           has-[*[data-slot=alert-actions]]:grid-rows-[auto_auto]

           /* cols */
           grid-cols-[0_1fr]
           has-[>svg]:grid-cols-[calc(var(--spacing)*4)_1fr]
           has-[*[data-slot=alert-close]]:grid-cols-[0_1fr_calc(var(--spacing)*4)]
           has-[>svg]:has-[*[data-slot=alert-close]]:grid-cols-[calc(var(--spacing)*4)_1fr_calc(var(--spacing)*4)]

           /* horizontal spacing:
              - only add grid gap when an icon exists
              - when NO icon, add left margin to the close button instead */
           has-[>svg]:gap-x-3
           [&:not(:has(>svg))_*[data-slot=alert-close]]:ml-3
           has-[>svg]:[&_*[data-slot=alert-close]]:ml-0

           items-start
           [&>svg]:size-4 [&>svg]:translate-y-0.5 [&>svg]:text-current";

  $variants = [
    'default' => 'bg-card text-card-foreground',
    'destructive' => 'text-destructive bg-card *:data-[slot=alert-description]:text-destructive/90 [&>svg]:text-current',
  ];

  $fallbackCols = $closable ? 'grid-cols-[0_1fr_calc(var(--spacing)*4)]' : '';
  $fallbackRows = $hasActions ? 'grid-rows-[auto_auto] gap-y-2' : '';

  $composed = $tw->merge($base, $fallbackCols, $fallbackRows, $variants[$variant] ?? $variants['default'], $attributes->get('class'));
@endphp

<div
  {{ $attributes->merge(['class' => $composed]) }}
  role="alert"
  data-slot="alert"
  x-data="{
    open: true,
    close() { this.open = false },
    init() {
      @if($timeout)
        setTimeout(() => this.close(), {{ (int) $timeout }});
      @endif
    }
  }"
  x-init="init()"
  x-show="open"
  @keydown.escape.window="close()"
  x-transition:leave="transition ease-in duration-300"
  x-transition:leave-start="opacity-100 scale-100"
  x-transition:leave-end="opacity-0 scale-95"
>
  {!! $slot !!}

  @if ($closable)
    <button
      type="button"
      data-slot="alert-close"
      title="{{ __('Close', 'wordpress-quickstart') }}"
      @click="close()"
      class="col-start-3 row-start-1 [&>svg]:size-4 self-start justify-self-end text-muted-foreground hover:text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 rounded-md cursor-pointer"
      aria-label="{{ __('Dismiss alert', 'wordpress-quickstart') }}"
    >
      <x-lucide-x />
      <span class="sr-only">{{ __('Close', 'wordpress-quickstart') }}</span>
    </button>
  @endif
</div>