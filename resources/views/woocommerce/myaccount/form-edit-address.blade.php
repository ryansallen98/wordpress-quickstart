{{-- resources/views/woocommerce/myaccount/form-edit-address.blade.php --}}

@php
    /** @var string $load_address */
    /** @var array $address */
    $page_title = ('billing' === $load_address)
        ? esc_html__('Billing address', 'woocommerce')
        : esc_html__('Shipping address', 'woocommerce');
@endphp

@php do_action('woocommerce_before_edit_account_address_form'); @endphp

@if (empty($load_address))
    @php wc_get_template('myaccount/my-address.php'); @endphp
@else
<form method="post" novalidate>
    <div>
        <div class="pb-4 border-b border-border">
            <h2 class="text-2xl font-semibold tracking-tight text-card-foreground">
                {!! apply_filters('woocommerce_my_account_edit_address_title', $page_title, $load_address) !!}
            </h2>
        </div>

        <div class="pt-4">
            <div class="woocommerce-address-fields">
                @php do_action("woocommerce_before_edit_address_form_{$load_address}"); @endphp

                <div class="woocommerce-address-fields__field-wrapper">
                    @php
                        foreach ($address as $key => $field) {
                            // Woo outputs full field HTML; wrapper grid handles layout.
                            woocommerce_form_field($key, $field, wc_get_post_data_by_key($key, $field['value']));
                        }
                    @endphp
                </div>

                @php do_action("woocommerce_after_edit_address_form_{$load_address}"); @endphp

                <div class="mt-6 flex items-center gap-3">
                    <button
                        type="submit"
                        name="save_address"
                        value="{{ esc_attr__('Save address', 'woocommerce') }}"
                        class="inline-flex items-center justify-center rounded-md border border-transparent bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                    >
                        {{ esc_html__('Save address', 'woocommerce') }}
                    </button>

                    {{-- Required nonce + action --}}
                    @php wp_nonce_field('woocommerce-edit_address', 'woocommerce-edit-address-nonce'); @endphp
                    <input type="hidden" name="action" value="edit_address">
                </div>
            </div>
        </div>
    </div>
</form>
@endif

@php do_action('woocommerce_after_edit_account_address_form'); @endphp