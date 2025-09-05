@props(['value','label'=>null,'disabled'=>false])

@php
  $label = $label ?? trim(preg_replace('/\s+/', ' ', $slot));
  $classes = $tw->merge(
    "data-[active=true]:bg-accent data-[active=true]:text-accent-foreground [&_svg:not([class*='text-'])]:text-muted-foreground 
     relative flex w-full cursor-default items-center gap-2 rounded-sm py-1.5 pr-8 pl-2 text-sm 
     outline-hidden select-none data-[disabled]:pointer-events-none data-[disabled]:opacity-50 [&_svg]:pointer-events-none 
     [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 *:[span]:last:flex *:[span]:last:items-center *:[span]:last:gap-2
     hover:bg-accent",
    $attributes->get('class')
  );
@endphp

<div
  role="option"
  :aria-selected="isSelected(@js($value)).toString()"
  :aria-disabled="@js($disabled)"
  x-init="$dispatch('x-select:register', { value: @js($value), label: @js($label), disabled: @js($disabled), __el: $el })"
  @dispose.window="$dispatch('x-select:unregister', { value: @js($value) })"
  x-show="filtered().some(i => i.value === @js($value))"
  :aria-hidden="(!filtered().some(i => i.value === @js($value))).toString()"
  :data-value="String(@js($value))"

  :data-active="(() => {
      const list = filtered();
      const idx = list.findIndex(i => i.value === @js($value));
      return idx === activeIndex;
    })()"
  :data-selected="isSelected(@js($value))"
  :data-disabled="@js($disabled)"

  @mouseenter="(() => {
      const list = filtered();
      const idx = list.findIndex(i => i.value === @js($value));
      if (idx >= 0) activeIndex = idx;
    })()"

  @mousedown.prevent="!@js($disabled) && selectValue(@js($value))"
  class="{{ $classes }}"
>
  <template x-if="isSelected(@js($value))">
    <span class="absolute right-2 flex size-3.5 items-center justify-center">
      <x-lucide-check aria-hidden="true" class="size-4"/>
    </span>
  </template>

  <span class="truncate">{{ $label }}</span>
</div>