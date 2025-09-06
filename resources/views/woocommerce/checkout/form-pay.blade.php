@extends('woocommerce.checkout.layouts.checkout')

@section('left')
  <h3 class="mb-4 text-2xl font-bold" id="order_review_heading">
    {{ __('Your order', 'woocommerce') }}
  </h3>

  @include(
    'woocommerce.checkout.partials.order',
    [
      'items' => $items,
      'order' => $order,
    ]
  )
@endsection

@section('right')
  Form Pay Right
@endsection
