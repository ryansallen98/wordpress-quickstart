@props([
    "variant" => "default",
    "closable" => false,
    "timeout" => null,
    "hasActions" => false,
])

@php
  $base = "relative w-full rounded-lg border h-fit px-4 py-3 text-sm grid
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
      "default" => "bg-card text-card-foreground",
      "destructive" => "text-destructive bg-card *:data-[slot=alert-description]:text-destructive/90 [&>svg]:text-current",
      "info" => "text-info bg-card *:data-[slot=alert-description]:text-card-foreground [&>svg]:text-current",
      "success" => "text-success bg-card *:data-[slot=alert-description]:text-card-foreground [&>svg]:text-current",
      "warning" => "text-warning bg-card *:data-[slot=alert-description]:text-card-foreground [&>svg]:text-current",
  ];

  $fallbackCols = $closable ? "grid-cols-[0_1fr_calc(var(--spacing)*4)]" : "";
  $fallbackRows = $hasActions ? "grid-rows-[auto_auto] gap-y-2" : "";

  $composed = $tw->merge($base, $fallbackCols, $fallbackRows, $variants[$variant] ?? $variants["default"], $attributes->get("class"));

  // a11y: role & live region per variant
  $isDestructive = in_array($variant, ["destructive", "danger"], true);
  $role = $isDestructive ? "alert" : "status";
  $ariaLive = $isDestructive ? "assertive" : "polite";
@endphp

<div
  {{ $attributes->merge(["class" => $composed]) }}
  role="{{ $role }}"
  aria-live="{{ $ariaLive }}"
  aria-atomic="true"
  data-slot="alert"
  x-data="{
    open: true,
    close() {
      this.open = false;
      // let parents react if needed (e.g., toast wrapper)
      this.$dispatch('alert:closed');
    },
    init() {
      @if ($timeout)
          setTimeout(()
          =>
          this.close(),
          {{ (int) $timeout }});
      @endif
    }
  }"
  x-init="init()"
  x-show="open"
  @keydown.escape.window="close()"
  x-transition:leave="transition duration-300 ease-in"
  x-transition:leave-start="scale-100 opacity-100"
  x-transition:leave-end="scale-95 opacity-0"
>
  {!! $slot !!}

  @if ($closable)
    <button
      type="button"
      data-slot="alert-close"
      title="{{ __("Close", "wordpress-quickstart") }}"
      @click="close()"
      class="text-muted-foreground hover:text-foreground focus:ring-ring col-start-3 row-start-1 cursor-pointer self-start justify-self-end rounded-md focus:ring-2 focus:ring-offset-2 focus:outline-none [&>svg]:size-4"
      aria-label="{{ __("Dismiss alert", "wordpress-quickstart") }}"
    >
      <x-lucide-x />
      <span class="sr-only">{{ __("Close", "wordpress-quickstart") }}</span>
    </button>
  @endif
</div>