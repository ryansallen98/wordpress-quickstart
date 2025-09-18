{{-- resources/views/woocommerce/myaccount/my-address.blade.php --}}

@php
    /** @var int $customer_id */
    $customer_id = get_current_user_id();

    if (!wc_ship_to_billing_address_only() && wc_shipping_enabled()) {
        $get_addresses = apply_filters(
            'woocommerce_my_account_get_addresses',
            [
                'billing' => __('Billing address', 'woocommerce'),
                'shipping' => __('Shipping address', 'woocommerce'),
            ],
            $customer_id
        );
    } else {
        $get_addresses = apply_filters(
            'woocommerce_my_account_get_addresses',
            [
                'billing' => __('Billing address', 'woocommerce'),
            ],
            $customer_id
        );
    }

    $desc = apply_filters(
        'woocommerce_my_account_my_address_description',
        esc_html__('The following addresses will be used on the checkout page by default.', 'woocommerce')
    );
@endphp

<div class="mb-6">
    <h1 class="text-2xl font-bold mb-2">{!! $title !!}</h1>
    <p class="text-sm text-muted-foreground mb-6">{{ $desc }}</p>
</div>

@php
    $is_two_col = (!wc_ship_to_billing_address_only() && wc_shipping_enabled());
@endphp

<div class="grid gap-6 {{ $is_two_col ? 'md:grid-cols-2' : 'grid-cols-1' }}">
    @foreach ($get_addresses as $name => $address_title)
        @php
            // 'billing' or 'shipping'
            $address = wc_get_account_formatted_address($name);
            $edit_url = wc_get_endpoint_url('edit-address', $name);
            $has_address = !empty($address);
        @endphp

        <div class="bg-card border border-border rounded-lg shadow-sm">
            <div class="p-6">
                <div class="flex items-start justify-between gap-4">
                    <h2 class="text-base font-semibold tracking-tight text-card-foreground">
                        {{ esc_html($address_title) }}
                    </h2>

                    <a href="{{ esc_url($edit_url) }}"
                        class="btn btn-outline">
                        <x-lucide-edit />

                        @if ($has_address)
                            {{-- translators: %s: Address title --}}
                            {{ sprintf(esc_html__('Edit %s', 'woocommerce'), esc_html($address_title)) }}
                        @else
                            {{ sprintf(esc_html__('Add %s', 'woocommerce'), esc_html($address_title)) }}
                        @endif
                    </a>
                </div>

                <div class="mt-4 text-sm leading-6 text-muted-foreground not-prose">
                    <address class="not-italic">
                        @if ($has_address)
                            {!! wp_kses_post($address) !!}
                        @else
                            {{ esc_html__('You have not set up this type of address yet.', 'woocommerce') }}
                        @endif
                    </address>

                    @php
                        /**
                         * Used to output content after core address fields.
                         *
                         * @param string $name Address type.
                         * @since 8.7.0
                         */
                        do_action('woocommerce_my_account_after_my_address', $name);
                    @endphp
                </div>
            </div>
        </div>
    @endforeach
</div>