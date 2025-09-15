@props(['maxHeight' => '320px'])

@php
  $panel = $tw->merge('z-50 w-fit overflow-hidden overflow-y-auto rounded-md border bg-popover text-popover-foreground shadow-md min-w-[8rem]', $attributes->get('class'));
@endphp

{{-- ⬇️ Teleport the panel to

<body> using Alpine --}}
  <template x-teleport="body">
    <div x-cloak x-show="open" x-transition.opacity.scale.origin.top role="listbox" :id="$id('listbox')"
      :aria-labelledby="triggerId" class="{{ $panel }}" :aria-activedescendant="activeOptionId() || null"
      @keydown.escape.prevent.stop="close(); $nextTick(() => requestAnimationFrame(() => $refs.trigger?.focus()))"
      @mousedown.prevent {{-- Floating UI init (unchanged, apart from using window.FloatingUIDOM below) --}} x-init="
  (async () => {
    const { computePosition, autoUpdate, offset, flip, shift, size } = window.FloatingUIDOM;

    const reference = $refs.trigger;
    const floating  = $el;

    // viewport-aware
    Object.assign(floating.style, { position: 'fixed', left: '0px', top: '0px' });

    let cleanup;
    const update = async () => {
      const { x, y } = await computePosition(reference, floating, {
        placement: 'bottom-start',
        strategy: 'fixed',
        middleware: [
          offset(6),
          flip({ fallbackPlacements: ['top-start'] }),
          shift({ padding: 8 }),
          size({
            apply({ availableHeight, availableWidth, rects, elements }) {
              // ✅ keep your height cap
              const capH = Math.max(160, Math.floor(availableHeight));
              elements.floating.style.setProperty('--combobox-max-h', capH + 'px');

              // ✅ NEW: ensure dropdown is at least as wide as the trigger
              const refW = Math.round(rects.reference.width);
              elements.floating.style.minWidth = refW + 'px';

              // (optional) prevent overflow on very narrow viewports
              elements.floating.style.maxWidth = Math.floor(availableWidth) + 'px';
            },
          }),
        ],
      });
      Object.assign(floating.style, { left: x + 'px', top: y + 'px' });
    };

    $watch('open', (o) => {
      if (o) {
        $nextTick(() => {
          cleanup = autoUpdate(reference, floating, update, {
            elementResize: true,  // ✅ updates if the trigger resizes
            ancestorScroll: true,
            ancestorResize: true,
          });
          update();
        });
      } else {
        cleanup && cleanup(); cleanup = null;
      }
    });

    $watch('query', () => { if (open) requestAnimationFrame(update) });
    $watch('activeIndex', () => { if (open) requestAnimationFrame(update) });
  })();
">
      {{-- … your search bar + options exactly as before … --}}
      <div class="border-b" x-show="searchable">
        @php $searchId = Str::uuid()->toString(); @endphp
        <label for="{{ $searchId }}" class="sr-only">
          {{ __('Search options', 'wordpress-quickstart') }}
        </label>
        <div class="relative">
          <input id="{{ $searchId }}" x-ref="search" data-select-search x-model="query" type="text" autofocus
            class="bg-background w-full rounded-none border-none px-2.5 py-2.5 pl-9 text-sm focus:border-transparent focus:ring-0 focus:outline-none focus-visible:border-transparent! focus-visible:ring-0! focus-visible:outline-none!"
            placeholder="{{ __('Search', 'wordpress-quickstart') }}"
            aria-label="{{ __('Search options', 'wordpress-quickstart') }}" @keydown.down.prevent.stop="moveActive(1)"
            @keydown.up.prevent.stop="moveActive(-1)" @keydown.enter.prevent.stop="selectActive()"
            @keydown.escape.prevent.stop="close()" @mousedown.stop />
          <x-lucide-search aria-hidden="true"
            class="text-muted-foreground pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2" />
        </div>
      </div>

      <div class="max-h-[var(--combobox-max-h,320px)] overflow-auto p-1" style="--combobox-max-h: {{ $maxHeight }}">
        {{ $slot }}

        <div x-show="filtered().length === 0"
          class="text-muted-foreground flex flex-row items-center justify-start gap-2 px-2 py-2 text-left text-xs select-none"
          aria-live="polite">
          <x-lucide-alert-circle class="size-4" />
          {{ __('No results found', 'wordpress-quickstart') }}
        </div>
      </div>
    </div>
  </template>