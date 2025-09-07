@php
  $show_terms =
    apply_filters('woocommerce_checkout_show_terms', true) &&
    function_exists('wc_terms_and_conditions_checkbox_enabled');
@endphp

@if ($show_terms)
  @php
    do_action('woocommerce_checkout_before_terms_and_conditions');
  @endphp

  <x-alert class="my-4 h-fit">
    <x-lucide-alert-circle aria-hidden="true" />
    <x-alert.title>
      {{ __('Terms and Conditions', 'woocommerce') }}
    </x-alert.title>

    <x-alert.description>
      <div class="whitespace-normal! h-fit">
        @php
          do_action('woocommerce_checkout_terms_and_conditions');
        @endphp
      </div>

      @if (wc_terms_and_conditions_checkbox_enabled())
        @php
          $is_checked = apply_filters('woocommerce_terms_is_checked_default', isset($_POST['terms']));
        @endphp

        <p class="form-row validate-required w-fit">
          <label
            class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox"
          >
            <input
              type="checkbox"
              class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox"
              name="terms"
              id="terms"
              @checked($is_checked)
              {{-- if your Blade doesn't support @checked, replace with: {!! checked($is_checked, true, false) !!} --}}
            />
            <span class="woocommerce-terms-and-conditions-checkbox-text whitespace-normal!">
              @php
                wc_terms_and_conditions_checkbox_text();
              @endphp
            </span>
            &nbsp;
            <abbr
              class="required"
              title="{{ esc_attr__('required', 'woocommerce') }}"
            >
              *
            </abbr>
          </label>

          <input type="hidden" name="terms-field" value="1" />
        </p>
      @endif
    </x-alert.description>
  </x-alert>

  @php
    do_action('woocommerce_checkout_after_terms_and_conditions');
  @endphp
@endif
