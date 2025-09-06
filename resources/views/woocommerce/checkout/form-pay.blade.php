@push('left')
  <h3 class="mb-4 text-2xl font-bold" id="order_review_heading">
    {{ __('Your order', 'woocommerce') }}
  </h3>

  <div class="mb-4 flex flex-col gap-4">
    @include(
      'woocommerce.checkout.partials.order',
      [
        'items' => $items,
        'order' => $order,
      ]
    )

    <x-separator />

    @include(
      'woocommerce.partials.totals',
      [
        'subtotals' => $subtotals,
        'order_total' => $order_total,
      ]
    )
  </div>
@endpush

@push('right')
  <div class="mx-auto flex-1 lg:max-w-[720px] lg:min-w-[560px] lg:p-12">
    @php
      do_action('woocommerce_review_order_before_cart_contents');
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
