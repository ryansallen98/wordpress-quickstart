@php
  // Compose classes using TailwindMerge
  $composed = $tw->merge(
    'absolute flex items-center justify-center
    data-[orientation=horizontal]:bottom-0 data-[orientation=horizontal]:left-0 
    data-[orientation=horizontal]:mb-4 data-[orientation=horizontal]:w-full
    data-[orientation=vertical]:bottom-0 data-[orientation=vertical]:right-0 
    data-[orientation=vertical]:mr-4 data-[orientation=vertical]:h-full',
    $attributes->get('class'),
  );
@endphp

<div
  {{ $attributes->merge(['class' => $composed]) }}
  x-bind:data-orientation="isVertical ? 'vertical' : 'horizontal'"
>
  <div
    role="tablist"
    tabindex="0"
    class="flex items-center justify-center gap-2 data-[orientation=vertical]:flex-col"
    aria-label="{{ __('Slide navigation', 'wordpress-quickstart') }}"
    {{-- Orientation-aware roving-tabindex controls; stop so the window handler doesn't double-fire --}}
    x-on:keydown.right.prevent.stop="if (!isVertical) focusDot((selectedIndex + 1) % slideCount)"
    x-on:keydown.left.prevent.stop="if (!isVertical) focusDot((selectedIndex - 1 + slideCount) % slideCount)"
    x-on:keydown.down.prevent.stop="if (isVertical) focusDot((selectedIndex + 1) % slideCount)"
    x-on:keydown.up.prevent.stop="if (isVertical) focusDot((selectedIndex - 1 + slideCount) % slideCount)"
    x-on:keydown.home.prevent.stop="focusDot(0)"
    x-on:keydown.end.prevent.stop="focusDot(slideCount - 1)"
    x-bind:data-orientation="isVertical ? 'vertical' : 'horizontal'"
  >
    <template x-for="i in slideCount" :key="i">
      <button
        type="button"
        data-dot
        role="tab"
        :aria-selected="selectedIndex === i - 1"
        :tabindex="selectedIndex === i - 1 ? 0 : -1"
        :data-dot-index="i - 1"
        class="cursor-pointer rounded-full focus:outline-none focus-visible:ring-2"
        x-on:click="to(i - 1)"
        x-bind:aria-label="@js(__('Go to slide :n', 'wordpress-quickstart')).replace(':n', i)"
      >
        <span
          class="hover:bg-primary hover:border-primary block rounded-full border transition"
          :class="[
            'h-2.5 w-2.5',
            selectedIndex === i - 1 ? 'scale-110 bg-primary border-primary' : 'opacity-50 bg-muted'
          ]"
          aria-hidden="true"
        ></span>
      </button>
    </template>
  </div>
</div>
