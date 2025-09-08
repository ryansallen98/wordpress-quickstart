@if (! wc_coupons_enabled())
  @php
    return;
  @endphp
@endif

<form
  {{-- class="checkout_coupon woocommerce-form-coupon" --}}
  method="post"
  id="woocommerce-checkout-form-coupon"
  aria-labelledby="coupon-legend"
  aria-describedby="coupon-hint"
  hidden
>
  <fieldset class="m-0 border-0 p-0">
    <legend id="coupon-legend" class="mb-2 font-bold">
      {{ esc_html__('Have a coupon?', 'woocommerce') }}
    </legend>

    <p id="coupon-hint" class="sr-only">
      {{ esc_html__('Enter your coupon code and press Apply coupon.', 'woocommerce') }}
    </p>

    <div class="flex items-stretch gap-0">
      <label for="coupon_code" class="sr-only">
        {{ esc_html__('Coupon code', 'woocommerce') }}
      </label>

      <input
        id="coupon_code"
        type="text"
        name="coupon_code"
        class="input-text rounded-r-none!"
        placeholder="{{ esc_attr__('Coupon code', 'woocommerce') }}"
        inputmode="text"
        autocapitalize="off"
        autocorrect="off"
        spellcheck="false"
        autocomplete="off"
        required
        aria-required="true"
        aria-describedby="coupon-hint coupon-feedback"
        {{-- force association to THIS form --}}
        form="woocommerce-checkout-form-coupon"
      />

      <button
        type="submit"
        class="btn btn-outline rounded-l-none border-l-0"
        name="apply_coupon"
        value="{{ esc_attr__('Apply coupon', 'woocommerce') }}"
        aria-describedby="coupon-hint"
        {{-- 🔒 ensure this submits ONLY the coupon form --}}
        form="woocommerce-checkout-form-coupon"
        formmethod="post"
        formaction="{{ esc_url(wc_get_checkout_url()) }}"
        formnovalidate
      >
        {{ esc_html__('Apply coupon', 'woocommerce') }}
      </button>
    </div>

    <div
      id="coupon-feedback"
      class="sr-only"
      role="status"
      aria-live="polite"
    ></div>
  </fieldset>
</form>