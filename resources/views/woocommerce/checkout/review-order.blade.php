<div
  class="shop_table woocommerce-checkout-review-order-table mb-4 flex flex-col gap-4"
>
  @php
    do_action('woocommerce_review_order_before_cart_contents');
  @endphp

  @include(
    'woocommerce.checkout.partials.order',
    [
      'items' => $cart_items,
    ]
  )

  @php
    do_action('woocommerce_review_order_after_cart_contents');
  @endphp

  <x-separator />

  @include('woocommerce.checkout.partials.form-coupon-proxy')

  @include(
    'woocommerce.partials.totals',
    [
      'subtotals' => $subtotals,
      'order_total' => $order_total,
    ]
  )
</div>
