{{-- resources/views/woocommerce/checkout/order-received.blade.php --}}

<?php defined('ABSPATH') || exit(); ?>

@php
  /**
   * Filter the message shown after a checkout is complete.
   *
   * @since 2.2.0
   *
   * @param string           $message The message.
   * @param \WC_Order|false  $order   The order created during checkout, or false if not available.
   */
  $message = apply_filters(
    'woocommerce_thankyou_order_received_text',
    esc_html(__('Your order has been received.', 'woocommerce')),
    $order,
  );
@endphp

<x-alert
  variant="success"
  class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received mb-8"
>
  <x-lucide-shopping-cart
    aria-hidden="true"
    class="text-success mr-2 inline h-5 w-5 flex-shrink-0"
  />
  <x-alert.title>
    {{ __('Thank you', 'woocommerce') }}
  </x-alert.title>
  <x-alert.description>
    {!! $message !!}
  </x-alert.description>
</x-alert>
