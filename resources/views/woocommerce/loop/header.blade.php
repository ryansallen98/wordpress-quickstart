@php
  if (! defined('ABSPATH')) { exit; }

  // Get title without echoing (WC echoes by default)
  $page_title = woocommerce_page_title(false);
@endphp

<header class="woocommerce-products-header">
  @if (apply_filters('woocommerce_show_page_title', true))
    <h1 class="text-2xl">
      {{ $page_title }}
    </h1>
  @endif

  {{-- Archive description (taxonomy or product archive) --}}
  @php do_action('woocommerce_archive_description'); @endphp
</header>