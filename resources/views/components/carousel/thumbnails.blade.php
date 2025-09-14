@props([
  // Array of thumbs in the exact slide order:
  // [['src' => '...', 'alt' => '...'], ...]
  'items' => [],
  // Tailwind size for each thumbnail
  'size' => 'w-16 h-16',
  // Gap between thumbs
  'gap' => 'gap-2',
])

@php
  $composed = $tw->merge(
    "mt-3 w-full",
    $attributes->get('class'),
  );
  // Normalize items to have src/alt keys
  $thumbs = array_map(fn ($t) => [
    'src' => $t['src'] ?? ($t['url'] ?? ''),
    'alt' => $t['alt'] ?? '',
  ], $items);
@endphp

<div {{ $attributes->merge(['class' => $composed]) }}>
<div
  role="tablist"
  aria-label="{{ __('Slide thumbnails', 'wordpress-quickstart') }}"
  class="thumbnails flex items-center overflow-x-auto no-scrollbar {{ $gap }} select-none cursor-grab p-1"
  tabindex="0"
  x-data="dragScroll()"
  x-on:mousedown="onDown"
  x-on:mousemove="onMove"
  x-on:mouseup.window="onUp"
  x-on:mouseleave="onUp"
  x-on:touchstart.passive="onTouchStart"
  x-on:touchmove.passive="onTouchMove"
  x-on:touchend.passive="onUp"
  x-on:dragstart.prevent
  x-on:click="maybeClick($event)"  {{-- prevent accidental clicks after drag --}}
  x-on:keydown.right.prevent.stop="focusDot((selectedIndex + 1) % slideCount)"
  x-on:keydown.left.prevent.stop="focusDot((selectedIndex - 1 + slideCount) % slideCount)"
  x-on:keydown.home.prevent.stop="focusDot(0)"
  x-on:keydown.end.prevent.stop="focusDot(Math.max(0, slideCount - 1))"
  x-bind:dir="(opts?.direction ?? 'ltr')"
  x-id="['viewport']"
>
    <template x-for="(t, i) in @js($thumbs)" :key="i">
      <button
        type="button"
        role="tab"
        :aria-selected="selectedIndex === i"
        :tabindex="selectedIndex === i ? 0 : -1"
        :data-thumb-index="i"
        class="relative shrink-0 rounded-md overflow-hidden focus:outline-none focus-visible:ring-2 transition"
        :class="selectedIndex === i
          ? 'ring-3 ring-ring'
          : 'opacity-60 hover:opacity-100 border-transparent'"
        x-on:click="to(i)"
        x-bind:aria-controls="$id('viewport')"
      >
        <img
          class="block object-cover {{ $size }}"
          :src="t.src"
          :alt="t.alt || ''"
          loading="lazy"
          decoding="async"
        />
      </button>
    </template>
  </div>

  {{-- Keep the active thumb in view --}}
  <div
    x-effect="
      $nextTick(() => {
        const el = $el.querySelector(`[data-thumb-index='${selectedIndex}']`)
        el?.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' })
      })
    "
  ></div>
</div>

{{-- Optional: hide scrollbars on WebKit/Firefox (scope to this component) --}}
<style>
  .no-scrollbar::-webkit-scrollbar { display: none; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>


@pushOnce('scripts')
<script>
  window.dragScroll = function () {
    return {
      isDown: false,
      startX: 0,
      startLeft: 0,
      // for click-cancel after drag
      moved: false,
      onDown(e) {
        this.isDown = true
        this.moved = false
        this.startX = e.pageX
        this.startLeft = e.currentTarget.scrollLeft
        e.currentTarget.classList.add('cursor-grabbing')
        e.preventDefault()
      },
      onMove(e) {
        if (!this.isDown) return
        const el = e.currentTarget
        const dx = e.pageX - this.startX
        if (Math.abs(dx) > 3) this.moved = true
        el.scrollLeft = this.startLeft - dx
      },
      onTouchStart(e) {
        const t = e.touches[0]
        this.isDown = true
        this.moved = false
        this.startX = t.pageX
        this.startLeft = e.currentTarget.scrollLeft
      },
      onTouchMove(e) {
        if (!this.isDown) return
        const t = e.touches[0]
        const el = e.currentTarget
        const dx = t.pageX - this.startX
        if (Math.abs(dx) > 3) this.moved = true
        el.scrollLeft = this.startLeft - dx
      },
      onUp(e) {
        this.isDown = false
        document.querySelectorAll('.cursor-grabbing').forEach(el => el.classList.remove('cursor-grabbing'))
      },
      maybeClick(e) {
        // If user dragged, swallow the click so you don't jump slides accidentally.
        if (this.moved) {
          e.stopPropagation()
          e.preventDefault()
        }
      },
    }
  }
</script>
@endpushOnce