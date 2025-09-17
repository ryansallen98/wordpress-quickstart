<div>
  @include('woocommerce.myaccount.partials.dashboard.header')

  @include('woocommerce.myaccount.partials.dashboard.stats')

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
    <div class="lg:col-span-2">
      @include('woocommerce.myaccount.partials.dashboard.recent-orders')
    </div>
    <div class="space-y-6">
      @include('woocommerce.myaccount.partials.dashboard.quick-actions')
      @include('woocommerce.myaccount.partials.dashboard.help-card')
    </div>
  </div>

  @include('woocommerce.myaccount.partials.dashboard.merchandising')

  {{-- keep Woo hooks for plugin compatibility but visually hidden --}}
  <div class="sr-only">
    @php
      do_action('woocommerce_account_dashboard');
      do_action('woocommerce_before_my_account');
      do_action('woocommerce_after_my_account');
    @endphp
  </div>
</div>