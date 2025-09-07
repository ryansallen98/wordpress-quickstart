<div class="woocommerce-billing-fields">
  @if (wc_ship_to_billing_address_only() && WC()->cart && WC()->cart->needs_shipping())
    <h3 class="text-xl font-bold mb-2">{{ __('Billing & Shipping', 'woocommerce') }}</h3>
  @else
    <h3 class="text-xl font-bold mb-2">{{ __('Billing details', 'woocommerce') }}</h3>
  @endif

  @php do_action('woocommerce_before_checkout_billing_form', $checkout); @endphp

  <div class="woocommerce-billing-fields__field-wrapper">
    @php
      $fields = $checkout->get_checkout_fields('billing');
    @endphp

    @foreach ($fields as $key => $field)
      @php woocommerce_form_field($key, $field, $checkout->get_value($key)); @endphp
    @endforeach
  </div>

  @php do_action('woocommerce_after_checkout_billing_form', $checkout); @endphp
</div>

@if (!is_user_logged_in() && $checkout->is_registration_enabled())
  <div class="woocommerce-account-fields">
    @if (!$checkout->is_registration_required())
      <p class="form-row form-row-wide create-account">
        <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
          <input
            class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox"
            id="createaccount"
            @checked(
              (true === $checkout->get_value('createaccount'))
              || (true === apply_filters('woocommerce_create_account_default_checked', false))
            )
            type="checkbox"
            name="createaccount"
            value="1"
          />
          <span>{{ __('Create an account?', 'woocommerce') }}</span>
        </label>
      </p>
    @endif

    @php do_action('woocommerce_before_checkout_registration_form', $checkout); @endphp

    @if ($checkout->get_checkout_fields('account'))
      <div class="create-account">
        @foreach ($checkout->get_checkout_fields('account') as $key => $field)
          @php woocommerce_form_field($key, $field, $checkout->get_value($key)); @endphp
        @endforeach
        <div class="clear"></div>
      </div>
    @endif

    @php do_action('woocommerce_after_checkout_registration_form', $checkout); @endphp
  </div>
@endif