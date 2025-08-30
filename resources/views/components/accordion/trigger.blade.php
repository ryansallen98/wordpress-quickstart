@props(['level' => 3])

@php
$base = "cursor-pointer focus-visible:border-ring focus-visible:ring-ring/50 flex w-full flex-1 items-start justify-between gap-4 rounded-md py-4 text-left text-sm font-medium transition-all outline-none hover:underline focus-visible:ring-[3px] disabled:pointer-events-none disabled:opacity-50 [&[aria-expanded=true]>svg]:rotate-180";
$class = $tw->merge($base, $attributes->get('class'));
@endphp

<h{{ $level }} class="flex">
  <button
    {{ $attributes->except('class') }}
    type="button"
    class="{{ $class }}"
    :id="`acc_${$id('acc')}`"
    :aria-controls="`panel_${$id('acc')}`"
    :aria-expanded="isOpen($id('acc'))"
    @click="toggle($id('acc'))"
    @keydown.arrow-down.prevent="moveFocus(1, $el)"
    @keydown.arrow-up.prevent="moveFocus(-1, $el)"
    @keydown.home.prevent="focusList[0]?.focus()"
    @keydown.end.prevent="focusList[focusList.length-1]?.focus()"
    x-init="registerTrigger($el)"
  >
    {{ $slot }}

    <x-lucide-chevron-down
      class="text-muted-foreground pointer-events-none size-4 shrink-0 translate-y-0.5 transition-transform duration-200"
    />
  </button>
</h{{ $level }}>