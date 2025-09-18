@php
  $input = 'input-text';
  $label = 'input-label';
  $help = 'mt-1 block text-xs text-muted-foreground';
  $checkbox = 'h-4 w-4 rounded border-input text-primary focus:ring-2 focus:ring-ring focus:ring-offset-2';
  $btn = 'btn btn-primary';
  $btnGhost = 'btn btn-outline';
@endphp

@push('body')
  <form method="post" class="woocommerce-ResetPassword lost_reset_password space-y-5" novalidate>
    <p class="{{ $help }}">
      @php
        // Keep Woo's filterable message exactly like core
        echo apply_filters(
          'woocommerce_lost_password_message',
          esc_html__(
            'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.',
            'woocommerce'
          )
        );
      @endphp
    </p>

    <div>
      <label for="user_login" class="{{ $label }}">
        {{ esc_html__('Username or email', 'woocommerce') }}
        <span class="required text-destructive" aria-hidden="true">*</span>
        <span class="sr-only">{{ esc_html__('Required', 'woocommerce') }}</span>
      </label>
      <input class="{{ $input }} woocommerce-Input woocommerce-Input--text input-text" type="text" name="user_login"
        id="user_login" autocomplete="username" required aria-required="true"
        value="{{ !empty($_POST['user_login']) && is_string($_POST['user_login']) ? esc_attr(wp_unslash($_POST['user_login'])) : '' }}" />
    </div>

    @php do_action('woocommerce_lostpassword_form'); @endphp

    <input type="hidden" name="wc_reset_password" value="true" />

    <div class="flex items-center gap-3">
      <button type="submit" class="{{ $btn }} woocommerce-Button button woocommerce-ResetPassword__submit"
        value="{{ esc_attr__('Reset password', 'woocommerce') }}">
        {{ esc_html__('Reset password', 'woocommerce') }}
      </button>

      {{-- Back to account/login --}}
      <a href="{{ esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))) }}" class="{{ $btnGhost }}">
        {{ esc_html__('Back to login', 'woocommerce') }}
      </a>
    </div>

    @php wp_nonce_field('lost_password', 'woocommerce-lost-password-nonce'); @endphp
  </form>
@endpush

@push('footer')
  {{-- Small-screen hint --}}
  <div class="mt-4 text-center text-sm text-muted-foreground">
    <p>
      {{ esc_html__("Remembered your password?", 'woocommerce') }}
      <a class="underline hover:no-underline!"
        href="{{ esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))) }}">
        {{ esc_html__('Log in', 'woocommerce') }}
      </a>
    </p>
  </div>
@endpush

@php do_action('woocommerce_before_lost_password_form'); @endphp

@include(
  'woocommerce.layouts.auth-shell',
  [
    'title' => __('Reset your password', 'woocommerce'),
  ]
)

@php do_action('woocommerce_after_lost_password_form'); @endphp