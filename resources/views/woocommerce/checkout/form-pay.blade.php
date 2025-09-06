@push('checkout_left')
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
@endpush

@push('checkout_right')
  Form Pay Right
@endpush

@include('woocommerce.checkout.layouts.shell', [
  'returnUrl'  => $returnUrl,
  'returnText' => $returnText,
])