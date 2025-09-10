<div class="relative flex min-h-[100dvh] items-start">
  <div
    class="bg-sidebar text-sidebar-foreground sticky top-0 min-h-[100dvh] hidden min-w-[16rem] overflow-y-auto border-r xl:flex flex-col">

    {{-- <div class="p-4 lg:p-8 mb-12 border-b">
      <div class="max-w-[100px] mx-auto">

      </div>
    </div> --}}

    <div class="p-4 flex-1 flex flex-col">
      @php
        do_action('woocommerce_account_navigation');
      @endphp
    </div>
  </div>
  <div class="w-full">
    <div class="bg-background sticky top-0 z-10 flex justify-between border-b px-4 py-2">
      <button class="btn btn-outline btn-icon">
        <x-lucide-panel-left aria-hidden="true" /><span
          class="sr-only">{{ __('Toggle navigation', 'wordpress-quickstart') }}</span>
      </button>
    </div>

    <div class="p-8">
      <h1 class="text-2xl font-bold mb-6">{{ $title }}</h1>
      @php
        do_action('woocommerce_account_content');
      @endphp
    </div>
  </div>
</div>