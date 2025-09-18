{{-- resources/views/woocommerce/global/form-login.blade.php --}}
@php
    if (!defined('ABSPATH')) {
        exit;
    }
    if (is_user_logged_in()) {
        return;
    }

    // tokens to match your auth UI
    $input = 'input-text';
    $label = 'input-label';
    $help  = 'mt-1 block text-xs text-muted-foreground';
    $btn   = 'btn btn-primary';
    $btnGhost = 'btn btn-outline';
@endphp

@push('body')
  <form
    class="woocommerce-form woocommerce-form-login login space-y-5"
    method="post"
    {!! !empty($hidden) ? 'style="display:none;"' : '' !!}
    novalidate
  >
    @php do_action('woocommerce_login_form_start'); @endphp

    @if (!empty($message))
      <div class="text-sm">
        {!! wpautop( wptexturize( $message ) ) !!}
      </div>
    @endif

    <div class="grid">
      <div class="form-row">
        <label for="username" class="{{ $label }}">
          {{ esc_html__('Username or email', 'woocommerce') }}
          <span class="required text-destructive" aria-hidden="true">*</span>
          <span class="sr-only">{{ esc_html__('Required', 'woocommerce') }}</span>
        </label>
        <input
          type="text"
          class="{{ $input }}"
          name="username"
          id="username"
          autocomplete="username"
          required
          aria-required="true"
          value="{{ !empty($_POST['username']) && is_string($_POST['username']) ? esc_attr(wp_unslash($_POST['username'])) : '' }}"
        />
      </div>

      <div class="form-row">
        <label for="password" class="{{ $label }}">
          {{ esc_html__('Password', 'woocommerce') }}
          <span class="required text-destructive" aria-hidden="true">*</span>
          <span class="sr-only">{{ esc_html__('Required', 'woocommerce') }}</span>
        </label>
        <input
          class="{{ $input }} woocommerce-Input woocommerce-Input--password"
          type="password"
          name="password"
          id="password"
          autocomplete="current-password"
          required
          aria-required="true"
        />
      </div>
    </div>

    @php do_action('woocommerce_login_form'); @endphp

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <label class="checkbox woocommerce-form__label woocommerce-form-login__rememberme">
        <input
          class="woocommerce-form__input woocommerce-form__input-checkbox"
          name="rememberme"
          type="checkbox"
          id="rememberme"
          value="forever"
        />
        <span>{{ esc_html__('Remember me', 'woocommerce') }}</span>
      </label>

      <div class="flex items-center gap-3">
        @php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); @endphp
        <input type="hidden" name="redirect" value="{{ esc_url( $redirect ?? '' ) }}" />
        <button
          type="submit"
          class="{{ $btn }} woocommerce-form-login__submit"
          name="login"
          value="{{ esc_attr__('Login', 'woocommerce') }}"
        >
          {{ esc_html__('Login', 'woocommerce') }}
        </button>
      </div>
    </div>

    <p class="woocommerce-LostPassword lost_password text-sm">
      <a class="underline hover:no-underline" href="{{ esc_url( wp_lostpassword_url() ) }}">
        {{ esc_html__('Lost your password?', 'woocommerce') }}
      </a>
    </p>

    @php do_action('woocommerce_login_form_end'); @endphp
  </form>
@endpush

@push('footer')
  {{-- Optional helper under the card --}}
  <div class="mt-4 text-center text-sm text-muted-foreground">
    <p>
      {{ esc_html__("Don't have an account?", 'woocommerce') }}
      <a class="underline hover:no-underline" href="{{ esc_url( get_permalink( get_option('woocommerce_myaccount_page_id') ) ) }}">
        {{ esc_html__('Create one', 'woocommerce') }}
      </a>
    </p>
  </div>
@endpush

@include('woocommerce.layouts.auth-shell', [
  'title' => __('Welcome back', 'woocommerce'),
])