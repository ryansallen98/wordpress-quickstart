@php
  $is_root_admin = is_super_admin() || current_user_can('manage_options');
@endphp

@if ($is_root_admin)
  <div 
    x-data="{ 
        open: JSON.parse(localStorage.getItem('debug-open') ?? 'false'), 
        toggleOpen() { 
            this.open = !this.open; 
            localStorage.setItem('debug-open', JSON.stringify(this.open)); 
        }
    }" 
    class="fixed bottom-4 left-4 overflow-hidden rounded-md bg-neutral-900" 
    :class="open ? 'w-2xl max-w-2xl' : ''"
  >
    <button
      class="flex cursor-pointer flex-row items-center justify-between gap-1 rounded-md bg-neutral-900 p-2 text-white hover:bg-neutral-800/50"
      :class="open ? 'w-full px-4' : 'aspect-square'"
      @click="toggleOpen"
      type="button"
    >
      <div class="flex items-center gap-2">
        <x-lucide-bug-off class="size-5"/>
        <span :class="open ? '' : 'sr-only'">Debug</span>
      </div>
      <x-lucide-minus class="size-4" x-bind:class="open ? '' : 'sr-only'" />
    </button>
    <div class="p-2 pt-4" :class="open ? '' : 'hidden'">
      <pre class="!m-0 max-h-[30rem] min-h-[30rem] overflow-auto rounded-md !bg-neutral-800 p-4 text-white">
@stack('afc_debug')
      </pre>
    </div>
  </div>
@endif