<div class="cart_totals w-full {{ WC()->customer->has_calculated_shipping() ? 'calculated_shipping' : '' }}">

    @php do_action('woocommerce_before_cart_totals') @endphp

    <h2 class="text-2xl font-semibold mb-2">{{ __('Cart totals', 'woocommerce') }}</h2>

    <div class="rounded-lg overflow-hidden shadow-sm border w-full">
        <table class="w-full border-separate border-spacing-0">
            <tbody>
                <tr class="cart-subtotal border-b last:border-b-0">
                    <th class="text-left px-4 py-3 font-medium">{{ __('Subtotal', 'woocommerce') }}</th>
                    <td class="text-right px-4 py-3" data-title="{{ __('Subtotal', 'woocommerce') }}">
                        {!! wc_cart_totals_subtotal_html() !!}</td>
                </tr>

                @foreach (WC()->cart->get_coupons() as $code => $coupon)
                    <tr class="cart-discount coupon-{{ \Illuminate\Support\Str::slug($code) }} border-b last:border-b-0">
                        <th class="text-left px-4 py-3 font-medium">{!! wc_cart_totals_coupon_label($coupon) !!}</th>
                        <td class="text-right px-4 py-3" data-title="{{ wc_cart_totals_coupon_label($coupon, false) }}">
                            {!! wc_cart_totals_coupon_html($coupon) !!}</td>
                    </tr>
                @endforeach

                @if (WC()->cart->needs_shipping() && WC()->cart->show_shipping())
                    @php do_action('woocommerce_cart_totals_before_shipping') @endphp
                    {!! wc_cart_totals_shipping_html() !!}
                    @php do_action('woocommerce_cart_totals_after_shipping') @endphp
                @elseif (WC()->cart->needs_shipping() && get_option('woocommerce_enable_shipping_calc') === 'yes')
                    <tr class="shipping border-b last:border-b-0">
                        <th class="text-left px-4 py-3 font-medium">{{ __('Shipping', 'woocommerce') }}</th>
                        <td class="text-right px-4 py-3" data-title="{{ __('Shipping', 'woocommerce') }}">
                            {!! woocommerce_shipping_calculator() !!}</td>
                    </tr>
                @endif

                @foreach (WC()->cart->get_fees() as $fee)
                    <tr class="fee border-b last:border-b-0">
                        <th class="text-left px-4 py-3 font-medium">{{ $fee->name }}</th>
                        <td class="text-right px-4 py-3" data-title="{{ $fee->name }}">{!! wc_cart_totals_fee_html($fee) !!}
                        </td>
                    </tr>
                @endforeach

                @php
                    $taxable_address = WC()->customer->get_taxable_address();
                    $estimated_text = '';
                    if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) {
                        if (WC()->customer->is_customer_outside_base() && !WC()->customer->has_calculated_shipping()) {
                            $estimated_text = ' <small>' . __('(estimated for', 'woocommerce') . ' ' . WC()->countries->estimated_for_prefix($taxable_address[0]) . WC()->countries->countries[$taxable_address[0]] . ')</small>';
                        }
                    }
                @endphp

                @if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax())
                    @if (get_option('woocommerce_tax_total_display') === 'itemized')
                        @foreach (WC()->cart->get_tax_totals() as $code => $tax)
                            <tr class="tax-rate tax-rate-{{ \Illuminate\Support\Str::slug($code) }} border-b last:border-b-0">
                                <th class="text-left px-4 py-3 font-medium">{!! $tax->label !!}{!! $estimated_text !!}</th>
                                <td class="text-right px-4 py-3" data-title="{{ $tax->label }}">{!! $tax->formatted_amount !!}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr class="tax-total border-b last:border-b-0">
                            <th class="text-left px-4 py-3 font-medium">
                                {!! WC()->countries->tax_or_vat() !!}{!! $estimated_text !!}</th>
                            <td class="text-right px-4 py-3" data-title="{{ WC()->countries->tax_or_vat() }}">
                                {!! wc_cart_totals_taxes_total_html() !!}</td>
                        </tr>
                    @endif
                @endif

                @php do_action('woocommerce_cart_totals_before_order_total') @endphp

                <tr class="order-total">
                    <th class="text-left px-4 py-4 font-bold text-lg">{{ __('Total', 'woocommerce') }}</th>
                    <td class="text-right px-4 py-4 font-bold text-lg" data-title="{{ __('Total', 'woocommerce') }}">
                        {!! wc_cart_totals_order_total_html() !!}</td>
                </tr>

                @php do_action('woocommerce_cart_totals_after_order_total') @endphp
            </tbody>
        </table>
    </div>

    <div class="wc-proceed-to-checkout mt-6 flex justify-end">
        @php do_action('woocommerce_proceed_to_checkout') @endphp
    </div>

    @php do_action('woocommerce_after_cart_totals') @endphp

</div>