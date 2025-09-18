@php
  $input = 'input-text';
  $label = 'input-label';
  $help = 'mt-1 block text-xs text-muted-foreground';
  $btn = 'btn btn-primary';
  $btnGhost = 'btn btn-outline';
@endphp

@push('body')
  <div class="space-y-5">
    <p class="text-sm">
      {{ esc_html(apply_filters(
    'woocommerce_lost_password_confirmation_message',
    esc_html__(
      'A password reset email has been sent to the email address on file for your account, but may take several minutes to show up in your inbox. Please wait at least 10 minutes before attempting another reset.',
      'woocommerce'
    )
  )) }}
    </p>

    <div class="flex flex-wrap items-center gap-3">
      <a href="{{ esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))) }}" class="{{ $btn }}">
        {{ esc_html__('Back to login', 'woocommerce') }}
      </a>
      <a href="{{ esc_url(wp_lostpassword_url()) }}" class="{{ $btnGhost }}">
        {{ esc_html__('Try again', 'woocommerce') }}
      </a>
      <a href="{{ esc_url(home_url('/')) }}" class="{{ $btnGhost }}">
        {{ esc_html__('Back to site', 'woocommerce') }}
      </a>
    </div>
  </div>
@endpush

@push('footer')
  {{-- Small-screen hint --}}
  <div class="mt-4 text-center text-sm text-muted-foreground">
    <p>
      {{ esc_html__("Didn’t get the email?", 'woocommerce') }}
      <span>{{ esc_html__('Check your spam folder or', 'woocommerce') }}</span>
      <a class="underline hover:no-underline!" href="{{ esc_url(wp_lostpassword_url()) }}">
        {{ esc_html__('request a new link', 'woocommerce') }}
      </a>.
    </p>
  </div>
@endpush

@php do_action('woocommerce_before_lost_password_confirmation_message'); @endphp

@include(
  'woocommerce.layouts.auth-shell',
  [
    'title' => __('Check your email', 'woocommerce'),
  ]
)

@php do_action('woocommerce_after_lost_password_confirmation_message'); @endphp