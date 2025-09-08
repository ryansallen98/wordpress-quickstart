@php(defined('ABSPATH') || exit())

<div class="woocommerce-order">
  @if ($order)
    @if ($order->has_status('failed'))
      @php(do_action('woocommerce_before_thankyou', $order->get_id()))
      <div class="max-w-lg mx-auto text-center h-[100dvh] flex flex-col items-center justify-center gap-4">
        <p
          class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"
        >
          {{ __('Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce') }}
        </p>

        <p
          class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions"
        >
          <a
            href="{{ esc_url($order->get_checkout_payment_url()) }}"
            class="button pay"
          >
            {{ __('Pay', 'woocommerce') }}
          </a>

          @if (is_user_logged_in())
            <a
              href="{{ esc_url(wc_get_page_permalink('myaccount')) }}"
              class="button pay"
            >
              {{ __('My account', 'woocommerce') }}
            </a>
          @endif
        </p>
      </div>
    @else
      @push('left')
        <div class="hidden lg:block">
          <h3 class="mb-4 text-2xl font-bold" id="order_review_heading_desktop">
            {{ __('Your Order', 'woocommerce') }}
          </h3>

          <div class="flex flex-col gap-4">
            @include('woocommerce.checkout.partials.order', ['items' => $items, 'order' => $order])

            <x-separator />

            @include(
              'woocommerce.partials.totals',
              [
                'subtotals' => $subtotals,
                'order_total' => $order_total,
              ]
            )
          </div>
        </div>
      @endpush

      @push('right')
        @php(do_action('woocommerce_before_thankyou', $order->get_id()))

        @php(wc_get_template('checkout/order-received.php', ['order' => $order]))
        <div>
          <h3 class="mb-4 text-xl font-bold" id="order_review_heading">
            {{ __('Order details', 'woocommerce') }}
          </h3>

          <ul
            class="woocommerce-order-overview woocommerce-thankyou-order-details order_details flex flex-col gap-2"
          >
            <li class="woocommerce-order-overview__order order">
              {{ __('Order number:', 'woocommerce') }}
              <strong>{!! $order->get_order_number() !!}</strong>
            </li>

            <li class="woocommerce-order-overview__date date">
              {{ __('Date:', 'woocommerce') }}
              <strong>
                {!! wc_format_datetime($order->get_date_created()) !!}
              </strong>
            </li>

            @if (is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email())
              <li class="woocommerce-order-overview__email email">
                {{ __('Email:', 'woocommerce') }}
                <strong>{!! $order->get_billing_email() !!}</strong>
              </li>
            @endif

            <li class="woocommerce-order-overview__total total">
              {{ __('Total:', 'woocommerce') }}
              <strong>{!! $order->get_formatted_order_total() !!}</strong>
            </li>

            @if ($order->get_payment_method_title())
              <li class="woocommerce-order-overview__payment-method method">
                {{ __('Payment method:', 'woocommerce') }}
                <strong>
                  {!! wp_kses_post($order->get_payment_method_title()) !!}
                </strong>
              </li>
            @endif
          </ul>
        </div>

        @if ($order->has_downloadable_item() && $order->is_download_permitted())
          <div class="mt-8 overflow-auto">
            @php(wc_get_template('order/order-downloads.php', [
              'downloads' => $order->get_downloadable_items(),
              'show_title' => true,
            ]))
          </div>
        @endif

        <div class="mt-8 block lg:hidden">
          <h3 class="mb-4 text-xl font-bold" id="order_review_heading_mobile">
            {{ __('Your Order', 'woocommerce') }}
          </h3>

          <div class="flex flex-col gap-4">
            @include('woocommerce.checkout.partials.order', ['items' => $items, 'order' => $order])

            <x-separator />

            @include(
              'woocommerce.partials.totals',
              [
                'subtotals' => $subtotals,
                'order_total' => $order_total,
              ]
            )
          </div>
        </div>

        <div class="mt-8">
          <h3 class="mb-4 text-xl font-bold" id="order_review_heading">
            {{ __('Your details', 'woocommerce') }}
          </h3>

          @include('woocommerce.checkout.partials.customer-address', ['order' => $order])

          {{-- @php(woocommerce_order_details_table($order->get_id())) --}}
          <div class="mt-8">
            @php(do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id()))
            @php(do_action('woocommerce_thankyou', $order->get_id()))
          </div>
        </div>

        <a
          href="{{ esc_url(wc_get_page_permalink('shop')) }}"
          class="button mt-8 w-full"
        >
          {{ __('Continue Shopping', 'woocommerce') }}
        </a>
      @endpush

      @include(
        'woocommerce.checkout.layouts.shell',
        [
          'returnUrl' => $returnUrl,
          'returnText' => $returnText,
        ]
      )
    @endif
  @else
    @php(wc_get_template('checkout/order-received.php', ['order' => false]))
  @endif
</div>
