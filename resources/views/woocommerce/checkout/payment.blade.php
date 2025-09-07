@if (! wp_doing_ajax())
  @php(do_action('woocommerce_review_order_before_payment'))
@endif

<div id="payment" class="woocommerce-checkout-payment mt-8">
  @if (WC()->cart && WC()->cart->needs_payment())
    <h3 id="payment-methods-heading" class="mb-2 text-xl font-bold">
      {{ esc_html__('Payment Method', 'woocommerce') }}
    </h3>

    <ul
      class="wc_payment_methods payment_methods methods mb-8 flex flex-col gap-2"
    >
      @if (! empty($available_gateways))
        @foreach ($available_gateways as $gateway)
          @php(wc_get_template('checkout/payment-method.php', ['gateway' => $gateway]))
        @endforeach
      @else
        <li>
          @php(wc_print_notice(
            apply_filters(
              'woocommerce_no_available_payment_methods_message',
              WC()->customer->get_billing_country()
                ? __(
                  'Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.',
                  'woocommerce',
                )
                : __(
                  'Please fill in your details above to see available payment methods.',
                  'woocommerce',
                ),
            ),
            'notice',
          ))
        </li>
      @endif
    </ul>
  @endif

  <div class="form-row place-order">
    <noscript>
      {{
        sprintf(
          /* translators: 1: <em> 2: </em> */
          __('Since your browser does not support JavaScript, or it is disabled, please ensure you click the %1$sUpdate Totals%2$s button before placing your order. You may be charged more than the amount stated above if you fail to do so.', 'woocommerce'),
          '<em>',
          '</em>',
        )
      }}
      @php($btnClass = wc_wp_theme_get_element_class_name('button'))
      <br />
      <button
        type="submit"
        class="button alt{{ $btnClass ? ' ' . $btnClass : '' }}"
        name="woocommerce_checkout_update_totals"
        value="{{ esc_attr__('Update totals', 'woocommerce') }}"
      >
        {{ __('Update totals', 'woocommerce') }}
      </button>
    </noscript>

    @php(wc_get_template('checkout/terms.php'))

    @php(do_action('woocommerce_review_order_before_submit'))

    {{-- Filter returns full HTML for the Place Order button --}}
    {!!
      apply_filters(
        'woocommerce_order_button_html',
        '<button type="submit" class="btn btn-primary btn-lg alt' .
          esc_attr(($btnClass = wc_wp_theme_get_element_class_name('button')) ? ' ' . $btnClass : '') .
          '" name="woocommerce_checkout_place_order" id="place_order" value="' .
          esc_attr($order_button_text) .
          '" data-value="' .
          esc_attr($order_button_text) .
          '">' .
          esc_html($order_button_text) .
          '</button>',
      )
    !!}

    @php(do_action('woocommerce_review_order_after_submit'))

    @php(wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'))
  </div>
</div>

@if (! wp_doing_ajax())
  @php(do_action('woocommerce_review_order_after_payment'))
@endif
