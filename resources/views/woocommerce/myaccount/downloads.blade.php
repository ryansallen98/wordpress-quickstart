@php
    $downloads     = WC()->customer->get_downloadable_products();
    $has_downloads = (bool) $downloads;
@endphp

<h1 class="text-2xl font-bold mb-6">{!! $title !!}</h1>

@php do_action('woocommerce_before_account_downloads', $has_downloads); @endphp

@if ($has_downloads)
    @php do_action('woocommerce_before_available_downloads'); @endphp

    {{-- WooCommerce core handles rendering the downloads table/list --}}
    @php do_action('woocommerce_available_downloads', $downloads); @endphp

    @php do_action('woocommerce_after_available_downloads'); @endphp
@else
    <x-alert class="cursor-default">
        <x-lucide-info aria-hidden="true" />
        <x-alert.title>
            {!! esc_html__('Heads Up', 'wordpress-quickstart') !!}
        </x-alert.title>
        <x-alert.description>
            {!! esc_html__('You have not made any purchases yet.', 'woocommerce') !!}
        </x-alert.description>

        <x-alert.actions>
            <a class="btn btn-primary btn-sm" href="{{ esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))) }}">
                <x-lucide-store aria-hidden="true" />
                {!! esc_html__('Browse products', 'woocommerce') !!}
            </a>
        </x-alert.actions>
    </x-alert>
@endif

@php do_action('woocommerce_after_account_downloads', $has_downloads); @endphp