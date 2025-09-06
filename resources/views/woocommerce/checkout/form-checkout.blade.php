@extends('woocommerce.checkout.layouts.checkout')

@section('left')
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
@endsection

@section('right')
  right
@endsection
