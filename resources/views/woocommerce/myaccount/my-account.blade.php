<div class="relative flex min-h-[100dvh] items-start">
  <div
    class="bg-sidebar text-sidebar-foreground sticky top-0 min-h-[100dvh] min-w-[16rem] overflow-y-auto border-r p-4 py-8 flex flex-col">
    @php
      do_action('woocommerce_account_navigation');
    @endphp
  </div>
  <div class="w-full">
    <div class="bg-background sticky top-0 z-10 flex justify-between border-b px-4 py-2">
      <button class="btn btn-outline btn-icon">
        <x-lucide-panel-left aria-hidden="true" /><span
          class="sr-only">{{ __('Toggle navigation', 'wordpress-quickstart') }}</span>
      </button>
    </div>

    <div class="p-8">
      @php
        do_action('woocommerce_account_content');
      @endphp
    </div>
  </div>
</div>