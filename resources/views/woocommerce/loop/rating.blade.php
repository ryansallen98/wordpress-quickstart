@php
  // Configurable (keep styling concerns in Blade)
  $icon = $icon ?? 'star';
  $iconSizeClass = $star_size ?? 'size-4';
  $iconClass = $star_class ?? 'h-4 w-4';
  $colorClass = $color_class ?? 'text-amber-500';
  $emptyClass = $empty_class ?? 'text-muted-foreground/50';

  $outlineComp = 'heroicon-o-' . $icon;
  $solidComp   = 'heroicon-s-' . $icon;
@endphp

@if ($rating_enabled ?? false)
  <x-tooltip>
    <x-slot:trigger>
      <div
        class="[&_svg:not([class*='size-'])]:{{ $iconSizeClass }} inline-flex items-center leading-none"
        role="img"
        aria-label="{{ $rating_label }}"
      >
        @foreach (($rating_fills ?? []) as $fill)
          <span class="{{ $iconClass }} relative inline-flex items-center justify-center align-middle">
            <x-dynamic-component
              :component="$outlineComp"
              class="absolute inset-0 {{ $iconClass }} {{ $emptyClass }}"
              aria-hidden="true"
            />
            @if ($fill > 0)
              <span class="absolute top-0 left-0 h-full overflow-hidden" style="width: {{ $fill }}%">
                <x-dynamic-component
                  :component="$solidComp"
                  class="{{ $iconClass }} {{ $colorClass }}"
                  aria-hidden="true"
                />
              </span>
            @endif
          </span>
        @endforeach

        @if (($showCount ?? true) && !empty($rating_count))
          <span class="text-primary/80 ml-1 text-sm">({{ $rating_count }})</span>
        @endif

        <span class="sr-only">{{ $rating_label }}</span>
      </div>
    </x-slot>

    <x-slot:content>
      {{ $rating_label }}
    </x-slot>
  </x-tooltip>
@endif