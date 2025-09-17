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
    <div class="bg-background sticky top-0 z-10 flex gap-4 border-b px-8 py-2">
      <button class="btn btn-outline btn-icon">
        <x-lucide-panel-left aria-hidden="true" /><span
          class="sr-only">{{ __('Toggle navigation', 'wordpress-quickstart') }}</span>
      </button>

      @include('partials.primary-navigation')

      <div class="flex flex-row">
        <x-theme.toggle />

        <a class="btn btn-ghost btn-icon" href="{{ get_permalink(get_option('woocommerce_cart_page_id')) }}">
          <x-heroicon-s-shopping-bag class="size-5" />
          <span class="sr-only">{{ __('Cart', 'wordpress-quickstart') }}</span>
        </a>
      </div>
    </div>

    <div class="p-8">
      @php
        do_action('woocommerce_account_content');
      @endphp
    </div>
  </div>
</div>