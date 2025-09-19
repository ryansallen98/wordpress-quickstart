@if (class_exists('WooCommerce'))
  @php $cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0; @endphp

  <div
    x-data="{ open: false }"
    x-effect="document.body.classList.toggle('overflow-hidden', open)"
    @keydown.escape.window="open = false"
    class="relative"
  >
    <!-- Trigger -->
    <button
      type="button"
      class="relative btn btn-ghost btn-icon"
      aria-haspopup="dialog"
      :aria-expanded="open.toString()"
      aria-controls="mini-cart-drawer"
      @click="open = true"
    >
      <x-heroicon-s-shopping-bag class="size-5" />
      <span class="sr-only">{{ __('Cart', 'wordpress-quickstart') }}</span>

      <span
        id="header-cart-count"
        class="absolute -top-1 -right-1 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-primary px-1 text-[11px] font-medium leading-none text-primary-foreground {{ $cart_count ? '' : 'hidden' }}"
      >{{ (int) $cart_count }}</span>
    </button>

    <!-- Overlay -->
    <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-40 bg-black/40" @click="open = false" aria-hidden="true"></div>

    <!-- Drawer -->
    <aside
      x-cloak
      x-show="open"
      x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="translate-x-full opacity-0"
      x-transition:enter-end="translate-x-0 opacity-100"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="translate-x-0 opacity-100"
      x-transition:leave-end="translate-x-full opacity-0"
      id="mini-cart-drawer"
      role="dialog"
      aria-modal="true"
      class="fixed inset-y-0 right-0 z-50 w-full max-w-md bg-card shadow-xl border-l border-border flex flex-col"
      @click.stop
    >
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-border px-4 py-3 bg-sidebar">
        <div class="flex items-center gap-2">
          <h2 class="text-xl font-semibold text-card-foreground">{{ __('Your cart', 'woocommerce') }}</h2>
        </div>
        <button type="button" class="btn btn-ghost btn-icon" @click="open = false" aria-label="{{ __('Close', 'woocommerce') }}">
            <x-lucide-x />
        </button>
      </div>

      <!-- Body: Woo replaces this whole div via fragments -->
      <div class="flex-1 overflow-auto flex flex-col">
          @php woocommerce_mini_cart(); @endphp
      </div>
    </aside>
  </div>
@endif