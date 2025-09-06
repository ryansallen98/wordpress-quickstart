@push('checkout_left')
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

@push('checkout_right')
  @php
    do_action('woocommerce_before_checkout_form', $checkout);
  @endphp

  <form
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
    @endif
  </form>
@endpush

@include('woocommerce.checkout.layouts.shell', [
  'returnUrl'  => $returnUrl,
  'returnText' => $returnText,
])