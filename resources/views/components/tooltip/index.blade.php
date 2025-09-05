@props([
  'placement'  => 'top',  // 'top' | 'bottom' | 'left' | 'right' (start/end handled automatically)
  'arrow'      => true,
  'openDelay'  => 80,     // ms
  'closeDelay' => 120,    // ms
])

<div
  x-data="{
    open: false,
    id: 'tt-' + (crypto?.randomUUID?.() ?? Math.random().toString(36).slice(2, 9)),
    /** desired base side: top/bottom/left/right */
    placement: @js($placement),
    /** actual placement computed by Floating UI, e.g. 'top', 'bottom-start' */
    _placed: @js($placement),
    _to: null,

    openWithDelay() { clearTimeout(this._to); this._to = setTimeout(() => this.open = true, {{ (int)$openDelay }}); },
    closeWithDelay() { clearTimeout(this._to); this._to = setTimeout(() => this.open = false, {{ (int)$closeDelay }}); },
    toggle() { this.open = !this.open },
    close() { this.open = false },
  }"
  class="relative inline-block"
  @keydown.escape.window="close()"
  @click.outside="close()"
>
  {{-- Trigger (adds role/tabindex only if not already focusable) --}}
  <div
    x-ref="trigger"
    @mouseenter="openWithDelay()"
    @mouseleave="closeWithDelay()"
    @focus="openWithDelay()"
    @blur="closeWithDelay()"
    {{-- @click.prevent="toggle()"  // enable if you want click-to-toggle --}}
    :aria-describedby="open ? id : null"
    x-init="
      const c = $el.firstElementChild
      const nativelyFocusable = c && (['A','BUTTON','INPUT','TEXTAREA','SELECT','SUMMARY'].includes(c.tagName) || c.hasAttribute('tabindex'))
      if (!nativelyFocusable) { $el.setAttribute('role','button'); $el.setAttribute('tabindex','0') }
    "
    data-role="tooltip-trigger"
  >
    {{ $trigger }}
  </div>

  {{-- Floating, teleported tooltip content (viewport-aware) --}}
  <template x-teleport="body">
    <div
      x-ref="content"
      x-show="open"
      x-cloak
      :id="id"
      role="tooltip"
      :aria-hidden="(!open).toString()"
      :data-state="open ? 'open' : 'closed'"
      :data-side="_placed.split('-')[0]"
      data-role="tooltip-content" 
      class="fixed z-50 w-fit max-w-sm min-w-max whitespace-nowrap rounded-md px-3 py-1.5 text-xs normal-case
             bg-primary text-primary-foreground shadow-md
             origin-[--radix-tooltip-content-transform-origin]
             will-change-[transform,left,top]
             data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95
             data-[side=top]:data-[state=open]:slide-in-from-bottom-2
             data-[side=bottom]:data-[state=open]:slide-in-from-top-2
             data-[side=left]:data-[state=open]:slide-in-from-right-2
             data-[side=right]:data-[state=open]:slide-in-from-left-2
             data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=closed]:zoom-out-95
             data-[side=top]:data-[state=closed]:slide-out-to-bottom-2
             data-[side=bottom]:data-[state=closed]:slide-out-to-top-2
             data-[side=left]:data-[state=closed]:slide-out-to-right-2
             data-[side=right]:data-[state=closed]:slide-out-to-left-2
             motion-reduce:!animate-none motion-reduce:!transition-none"
      @mouseenter="openWithDelay()"
      @mouseleave="closeWithDelay()"

      x-init="
        const { computePosition, autoUpdate, offset, flip, shift, arrow: arrowMw } = window.FloatingUIDOM;

        const reference = $refs.trigger;
        const floating  = $el;
        const arrowEl   = floating.querySelector('[data-role=tooltip-arrow]');
        Object.assign(floating.style, { position: 'fixed', left: '0px', top: '0px' });

        let cleanup;

        const update = async () => {
          const baseSide = $data.placement; // 'top' | 'bottom' | ...
          const { x, y, placement: placed, middlewareData } =
            await computePosition(reference, floating, {
              placement: baseSide,    // start/end will be chosen automatically
              strategy: 'fixed',
              middleware: [
                offset(8),
                flip({ fallbackPlacements: ['top','bottom','right','left'] }),
                shift({ padding: 8 }),
                arrowEl ? arrowMw({ element: arrowEl, padding: 6 }) : null,
              ].filter(Boolean),
            });

          $data._placed = placed;
          Object.assign(floating.style, { left: x + 'px', top: y + 'px' });

          // Arrow positioning
          if (arrowEl && middlewareData.arrow) {
            const { x: ax = null, y: ay = null } = middlewareData.arrow;
            const side = placed.split('-')[0];

            // Reset all sides first
            Object.assign(arrowEl.style, { left: '', top: '', right: '', bottom: '' });

            if (ax != null) arrowEl.style.left = ax + 'px';
            if (ay != null) arrowEl.style.top  = ay + 'px';

            // Pull arrow out on the opposite side
            const OFFSET = 5; // px
            if (side === 'top')    arrowEl.style.bottom = (-OFFSET) + 'px';
            if (side === 'bottom') arrowEl.style.top    = (-OFFSET) + 'px';
            if (side === 'left')   arrowEl.style.right  = (-OFFSET) + 'px';
            if (side === 'right')  arrowEl.style.left   = (-OFFSET) + 'px';
          }
        };

        // Start/stop reactive autoUpdate
        $watch('open', (o) => {
          if (o) {
            $nextTick(() => {
              cleanup = autoUpdate(reference, floating, update, {
                ancestorScroll: true,
                ancestorResize: true,
                elementResize: true,
              });
              update();
            });
          } else {
            cleanup && cleanup(); cleanup = null;
          }
        });

        // React to prop changes
        $watch('placement', () => { if (open) requestAnimationFrame(update) });

        // Recompute on content changes you already react to (optional hooks)
        // Example: $watch some Alpine data here if content size changes while open
      "
    >
      {{ $content }}

      @if ($arrow)
        <div
          aria-hidden="true"
          role="presentation"
          data-role="tooltip-arrow"
          class="absolute size-2.5 bg-primary rounded-[2px]"
          style="transform: rotate(45deg);"
        ></div>
      @endif
    </div>
  </template>
</div>