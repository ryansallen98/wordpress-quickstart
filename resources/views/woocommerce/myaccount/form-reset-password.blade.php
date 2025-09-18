@php
  defined('ABSPATH') || exit;

  $input = 'input-text';
  $label = 'input-label';
  $help  = 'mt-1 block text-xs text-muted-foreground';
  $btn   = 'btn btn-primary';
  $btnGhost = 'btn btn-outline';
@endphp

@push('body')
  <div class="space-y-5">
    <p class="text-sm">
      {!! apply_filters(
        'woocommerce_reset_password_message',
        esc_html__( 'Enter a new password below.', 'woocommerce' )
      ) !!}
    </p>

    <form method="post" class="woocommerce-ResetPassword lost_reset_password space-y-5" novalidate>
      <div class="grid">
        <div class="form-row">
          <label for="password_1" class="{{ $label }}">
            {{ esc_html__('New password', 'woocommerce') }}
            <span class="required text-destructive" aria-hidden="true">*</span>
            <span class="sr-only">{{ esc_html__('Required', 'woocommerce') }}</span>
          </label>
          <input
            type="password"
            class="{{ $input }} woocommerce-Input woocommerce-Input--text input-text"
            name="password_1"
            id="password_1"
            autocomplete="new-password"
            required
            aria-required="true"
          />
        </div>

        <div class="form-row">
          <label for="password_2" class="{{ $label }}">
            {{ esc_html__('Re-enter new password', 'woocommerce') }}
            <span class="required text-destructive" aria-hidden="true">*</span>
            <span class="sr-only">{{ esc_html__('Required', 'woocommerce') }}</span>
          </label>
          <input
            type="password"
            class="{{ $input }} woocommerce-Input woocommerce-Input--text input-text"
            name="password_2"
            id="password_2"
            autocomplete="new-password"
            required
            aria-required="true"
          />
        </div>
      </div>

      {{-- Hidden fields from core --}}
      <input type="hidden" name="reset_key" value="{{ esc_attr( $args['key'] ?? '' ) }}" />
      <input type="hidden" name="reset_login" value="{{ esc_attr( $args['login'] ?? '' ) }}" />

      @php do_action('woocommerce_resetpassword_form'); @endphp

      <input type="hidden" name="wc_reset_password" value="true" />

      <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="{{ $btn }} woocommerce-Button button" value="{{ esc_attr__('Save', 'woocommerce') }}">
          {{ esc_html__('Save', 'woocommerce') }}
        </button>
        <a href="{{ esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))) }}" class="{{ $btnGhost }}">
          {{ esc_html__('Back to login', 'woocommerce') }}
        </a>
        <a href="{{ esc_url(home_url('/')) }}" class="{{ $btnGhost }}">
          {{ esc_html__('Back to site', 'woocommerce') }}
        </a>
      </div>

      @php wp_nonce_field('reset_password', 'woocommerce-reset-password-nonce'); @endphp
    </form>
  </div>
@endpush

@push('footer')
  <div class="mt-4 text-center text-sm text-muted-foreground">
    <p>
      {{ esc_html__("Tip:", 'woocommerce') }}
      <span>{{ esc_html__('Use a long, unique passphrase. Avoid reusing passwords from other sites.', 'woocommerce') }}</span>
    </p>
  </div>
@endpush

@php do_action('woocommerce_before_reset_password_form'); @endphp

@include(
  'woocommerce.layouts.auth-shell',
  [
    'title' => __('Create a new password', 'woocommerce'),
  ]
)

@php do_action('woocommerce_after_reset_password_form'); @endphp