@php
  $p = $product ?? ($GLOBALS['product'] ?? null);
  if (! $p) {
    return;
  }

  $avg = (float) $p->get_average_rating();
  $count = (int) $p->get_rating_count();

  // Format: drop trailing .0, keep decimals if needed
  $avg_display = floor($avg) == $avg ? (int) $avg : number_format($avg, 1);

  // Config
  $icon = 'star';
  $iconSizeClass = $star_size ?? 'size-4'; // used by your arbitrary selector (optional)
  $iconClass = $star_class ?? 'h-4 w-4';   // applied to each icon
  $colorClass = $color_class ?? 'text-amber-500';
  $emptyClass = $empty_class ?? 'text-muted-foreground/50';

  $outlineComp = 'heroicon-o-' . $icon;
  $solidComp   = 'heroicon-s-' . $icon;

  $label = sprintf(
    esc_html__('Rated %s out of 5', 'woocommerce'),
    $avg_display
  );
@endphp

@if (wc_review_ratings_enabled() && $avg > 0)
  {{-- Tooltip Wrapper --}}
  <x-tooltip>
    {{-- Rating --}}
    <x-slot:trigger>
      <div
        class="[&_svg:not([class*='size-'])]:{{ $iconSizeClass }} inline-flex items-center leading-none"
        role="img"
        aria-label="{{ $label }}"
      >
        @for ($i = 1; $i <= 5; $i++)
          @php
            // Percentage of this icon to fill (0–100)
            $fill = min(max($avg - ($i - 1), 0), 1) * 100;
          @endphp

          <span
            class="{{ $iconClass }} relative inline-flex items-center justify-center align-middle"
          >
            {{-- Empty/outline icon as background --}}
            <x-dynamic-component
              :component="$outlineComp"
              class="absolute inset-0 {{ $iconClass }} {{ $emptyClass }}"
              aria-hidden="true"
            />

            @if ($fill > 0)
              {{-- Filled overlay with dynamic width --}}
              <span
                class="absolute top-0 left-0 h-full overflow-hidden"
                style="width: {{ $fill }}%"
              >
                <x-dynamic-component
                  :component="$solidComp"
                  class="{{ $iconClass }} {{ $colorClass }}"
                  aria-hidden="true"
                />
              </span>
            @endif
          </span>
        @endfor

        @if (($showCount ?? true) && $count)
          <span class="text-primary/80 ml-1 text-sm">({{ $count }})</span>
        @endif

        {{-- SR-only redundancy is optional since aria-label is on the wrapper --}}
        <span class="sr-only">{{ $label }}</span>
      </div>
    </x-slot>

    {{-- Tooltip Content --}}
    <x-slot:content>
      {{ $label }}
    </x-slot>
  </x-tooltip>
@endif
