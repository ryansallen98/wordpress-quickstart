@php
  if (! defined('ABSPATH')) exit();

  $accountMenuItems = wc_get_account_menu_items();
  $user = wp_get_current_user();
  $isSupplier = in_array('supplier', (array) $user->roles, true);

  $accountMenuIcons = [
    'dashboard'         => 'home',
    'orders'            => 'shopping-bag',
    'downloads'         => 'download',
    'edit-address'      => 'map-pin',
    'payment-methods'   => 'credit-card',
    'edit-account'      => 'user',
    'customer-logout'   => 'log-out',
    'supplier-orders'   => 'truck',
    'supplier-products' => 'boxes',
  ];

  if (! $isSupplier) {
    unset($accountMenuItems['supplier-orders'], $accountMenuItems['supplier-products']);
  }

  do_action('woocommerce_before_account_navigation');
@endphp

<nav class="woocommerce-MyAccount-navigation w-full h-full flex-1 flex flex-col" aria-label="{{ __('Account pages', 'woocommerce') }}">
  <ul class="flex flex-col gap-2 w-full h-full flex-1">
    <li>
      <a class="btn btn-ghost w-full justify-start gap-2" href="{{ esc_url(wc_get_page_permalink('shop')) }}">
        {{-- Dashboard icon --}}
        <x-lucide-chevron-left aria-hidden="true" />
        {{ __('Back to shop', 'woocommerce') }}
      </a>
    </li>
    @foreach ($accountMenuItems as $endpoint => $label)
      @php
        $icon     = $accountMenuIcons[$endpoint] ?? 'circle';
        $isLogout = $endpoint === 'customer-logout';
      @endphp

      <li class="{{ wc_get_account_menu_item_classes($endpoint) }} w-full flex flex-col {{ $isLogout ? 'mt-auto' : '' }}">
        <a
          href="{{ esc_url(wc_get_account_endpoint_url($endpoint)) }}"
          data-active="{{ wc_is_current_account_menu_item($endpoint) ? 'true' : 'false' }}"
          class="btn {{ $isLogout ? 'btn-primary' : 'btn-ghost' }} w-full justify-start gap-2 data-[active=true]:bg-muted"
          @if (wc_is_current_account_menu_item($endpoint)) aria-current="page" @endif
        >
          {{-- Dynamic Lucide Blade icon --}}
          <x-dynamic-component :component="'lucide-'.$icon" class="w-4 h-4" aria-hidden="true" />

          {{ esc_html($label) }}
        </a>
      </li>
    @endforeach
  </ul>
</nav>

@php do_action('woocommerce_after_account_navigation'); @endphp