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
  <form
    id="order_review"
    method="post"
    class="mx-auto flex-1 lg:max-w-[720px] lg:min-w-[560px] lg:p-12"
  >
    @php
      do_action('woocommerce_pay_order_before_payment');
    @endphp

    <h3 id="payment-methods-heading" class="mb-2 text-xl font-bold">
      {{ esc_html__('Payment Method', 'woocommerce') }}
    </h3>

    <div id="payment">
      <?php if ( $order->needs_payment() ) : ?>

      <ul
        class="wc_payment_methods payment_methods methods mb-8 flex flex-col gap-2"
      >
        <?php
        if (! empty($available_gateways)) {
          foreach ($available_gateways as $gateway) {
            wc_get_template('checkout/payment-method.php', ['gateway' => $gateway]);
          }
        } else {
          echo '<li>';
          wc_print_notice(
            apply_filters(
              'woocommerce_no_available_payment_methods_message',
              esc_html__(
                'Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.',
                'woocommerce',
              ),
            ),
            'notice',
          ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
          echo '</li>';
        }
        ?>
      </ul>

      <?php endif; ?>

      <div class="form-row">
        <input type="hidden" name="woocommerce_pay" value="1" />

        <?php wc_get_template('checkout/terms.php'); ?>

        <?php do_action('woocommerce_pay_order_before_submit'); ?>

        <?php echo apply_filters(
          'woocommerce_pay_order_button_html',
          '<button type="submit" class="btn btn-primary btn-lg alt' .
            esc_attr(
              wc_wp_theme_get_element_class_name('button')
                ? ' ' . wc_wp_theme_get_element_class_name('button')
                : '',
            ) .
            '" id="place_order" value="' .
            esc_attr($order_button_text) .
            '" data-value="' .
            esc_attr($order_button_text) .
            '">' .
            esc_html($order_button_text) .
            '</button>',
        );
        // @codingStandardsIgnoreLine
        ?>

        <?php do_action('woocommerce_pay_order_after_submit'); ?>

        <?php wp_nonce_field('woocommerce-pay', 'woocommerce-pay-nonce'); ?>
      </div>
    </div>
  </form>
@endpush

@include(
  'woocommerce.checkout.layouts.shell',
  [
    'returnUrl' => $returnUrl,
    'returnText' => $returnText,
  ]
)
