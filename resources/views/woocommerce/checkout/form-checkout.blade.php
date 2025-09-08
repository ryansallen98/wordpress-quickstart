{{-- If checkout registration is disabled and not logged in, the user cannot checkout. --}}
@if (! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in())
  {{ apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')) }}

  @php
    return;
  @endphp
@endif

{{-- Checkout Form --}}
@push('left')
  @php
    do_action('woocommerce_checkout_before_order_review_heading');
  @endphp

  <h3 class="mb-4 text-2xl font-bold" id="order_review_heading">
    {{ __('Your order', 'woocommerce') }}
  </h3>

  @php
    do_action('woocommerce_checkout_before_order_review');
  @endphp

  <div id="order_review" class="woocommerce-checkout-review-order">
    @php
      do_action('woocommerce_checkout_order_review');
    @endphp
  </div>

  @php
    do_action('woocommerce_checkout_after_order_review');
  @endphp
@endpush

@push('right')
  <div
    class="h-full"
    x-data="{ tab: 'checkout'}"
    x-init="if (location.hash === '#login') tab = 'login'"
  >
    @php
      do_action('woocommerce_before_checkout_form', $checkout);
    @endphp

    <form
      x-show="tab === 'checkout'"
      name="checkout"
      method="post"
      class="checkout woocommerce-checkout"
      action="<?php echo esc_url(wc_get_checkout_url()); ?>"
      enctype="multipart/form-data"
      aria-label="<?php echo esc_attr__('Checkout', 'woocommerce'); ?>"
    >
      @if ($checkout->get_checkout_fields())
        @php
          do_action('woocommerce_checkout_before_customer_details');
        @endphp

        <div class="col2-set" id="customer_details">
          <div class="col-1">
            @php
              do_action('woocommerce_checkout_billing');
            @endphp
          </div>

          <div class="col-2">
            @php
              do_action('woocommerce_checkout_shipping');
            @endphp
          </div>
        </div>

        @php
          do_action('woocommerce_checkout_after_customer_details');
        @endphp

        @php
          woocommerce_checkout_payment();
        @endphp
      @endif
    </form>

    @php
      do_action('woocommerce_after_checkout_form', $checkout);
    @endphp
  </div>
@endpush

@include(
  'woocommerce.checkout.layouts.shell',
  [
    'returnUrl' => $returnUrl,
    'returnText' => $returnText,
  ]
)
