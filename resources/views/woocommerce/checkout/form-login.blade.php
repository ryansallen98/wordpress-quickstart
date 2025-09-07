@if ($showLogin)
  <button
    type="button"
    x-show="tab === 'checkout'"
    @click="tab = 'login'"
    class="btn btn-ghost btn-lg text-muted-foreground mb-4 h-auto w-full border-2 border-dashed p-4 lg:mb-8"
    :aria-expanded="(tab === 'login').toString()"
    aria-controls="wc-login-panel"
  >
    <x-lucide-user />
    {{ __('Already have an account? Log in', 'woocommerce') }}
  </button>

  <div
    id="wc-login-panel"
    x-show="tab === 'login'"
    x-cloak
    class="mx-auto flex h-full max-w-md flex-col items-start justify-center"
  >
    <button
      type="button"
      class="btn btn-ghost mb-4 inline-flex px-0! lg:hidden"
      @click="tab = 'checkout'"
    >
      <x-lucide-chevron-left aria-hidden="true" />
      {{ __('Return to Checkout', 'woocommerce') }}
    </button>

    <div class="mb-4 w-full">
      <h2 class="mb-2 text-2xl font-bold">
        {{ __('Returning customer?', 'woocommerce') }}
      </h2>
      <p>
        {{ __('Welcome back, please log in to your account.', 'woocommerce') }}
      </p>
    </div>

    <form
      class="woocommerce-form woocommerce-form-login login w-full"
      method="post"
      action="{{ $checkoutUrl }}"
    >
      @php(wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'))
      @php(do_action('woocommerce_login_form_start'))

      <p class="form-row w-full">
        <label for="username">
          {{ __('Username or email', 'woocommerce') }}&nbsp;
          <span class="required">*</span>
        </label>
        <input
          type="text"
          class="input-text"
          name="username"
          id="username"
          autocomplete="username"
        />
      </p>

      <p class="form-row">
        <label for="password">
          {{ __('Password', 'woocommerce') }}&nbsp;
          <span class="required">*</span>
        </label>
          <input
            class="input-text woocommerce-Input"
            type="password"
            name="password"
            id="password"
            autocomplete="current-password"
            required
            aria-required="true"
          />
      </p>

      <div class="clear"></div>

      @php(do_action('woocommerce_login_form'))

      <p class="form-row">
        <label
          class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme"
        >
          <input
            class="woocommerce-form__input woocommerce-form__input-checkbox"
            name="rememberme"
            type="checkbox"
            id="rememberme"
            value="forever"
          />
          <span>{{ __('Remember me', 'woocommerce') }}</span>
        </label>

        <button
          type="submit"
          class="button"
          name="login"
          value="{{ __('Log in', 'woocommerce') }}"
        >
          {{ __('Log in', 'woocommerce') }}
        </button>
      </p>

      <p class="lost_password flex w-full justify-center">
        <a href="{{ $lostPasswordUrl }}">
          {{ __('Lost your password?', 'woocommerce') }}
        </a>
      </p>

      @php(do_action('woocommerce_login_form_end'))
    </form>

    <button
      type="button"
      class="btn btn-ghost mt-12 hidden lg:inline-flex"
      @click="tab = 'checkout'"
    >
      <x-lucide-chevron-left aria-hidden="true" />
      {{ __('Return to Checkout', 'woocommerce') }}
    </button>
  </div>
@endif
