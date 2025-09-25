@php
    /**
     * Shipping Methods Display
     *
     * @see https://woocommerce.com/document/template-structure/
     * @package WooCommerce\Templates
     * @version 8.8.0
     */

    defined('ABSPATH') || exit;

    $formatted_destination    = $formatted_destination ?? WC()->countries->get_formatted_address($package['destination'], ', ');
    $has_calculated_shipping  = !empty($has_calculated_shipping);
    $show_shipping_calculator = !empty($show_shipping_calculator);
    $calculator_text          = '';
@endphp

<tr class="woocommerce-shipping-totals shipping">
    <th>{!! wp_kses_post($package_name) !!}</th>
    <td data-title="{{ esc_attr($package_name) }}">
        @if (!empty($available_methods) && is_array($available_methods))
            <ul id="shipping_method" class="woocommerce-shipping-methods">
                @foreach ($available_methods as $method)
                    <li>
                        @if (count($available_methods) > 1)
                            <input type="radio"
                                   name="shipping_method[{{ $index }}]"
                                   data-index="{{ $index }}"
                                   id="shipping_method_{{ $index }}_{{ esc_attr(sanitize_title($method->id)) }}"
                                   value="{{ esc_attr($method->id) }}"
                                   class="shipping_method"
                                   {!! checked($method->id, $chosen_method, false) !!} />
                        @else
                            <input type="hidden"
                                   name="shipping_method[{{ $index }}]"
                                   data-index="{{ $index }}"
                                   id="shipping_method_{{ $index }}_{{ esc_attr(sanitize_title($method->id)) }}"
                                   value="{{ esc_attr($method->id) }}"
                                   class="shipping_method" />
                        @endif
                        <label for="shipping_method_{{ $index }}_{{ esc_attr(sanitize_title($method->id)) }}">
                            {!! wc_cart_totals_shipping_method_label($method) !!}
                        </label>
                        {!! do_action('woocommerce_after_shipping_rate', $method, $index) !!}
                    </li>
                @endforeach
            </ul>
            @if (is_cart())
                <p class="woocommerce-shipping-destination">
                    @if ($formatted_destination)
                        {!! sprintf(
                            esc_html__('Shipping to %s.', 'woocommerce') . ' ',
                            '<strong>' . esc_html($formatted_destination) . '</strong>'
                        ) !!}
                        @php $calculator_text = esc_html__('Change address', 'woocommerce'); @endphp
                    @else
                        {!! apply_filters(
                            'woocommerce_shipping_estimate_html',
                            __('Shipping options will be updated during checkout.', 'woocommerce')
                        ) !!}
                    @endif
                </p>
            @endif
        @elseif (!$has_calculated_shipping || !$formatted_destination)
            @if (is_cart() && get_option('woocommerce_enable_shipping_calc') === 'no')
                {!! apply_filters(
                    'woocommerce_shipping_not_enabled_on_cart_html',
                    __('Shipping costs are calculated during checkout.', 'woocommerce')
                ) !!}
            @else
                {!! apply_filters(
                    'woocommerce_shipping_may_be_available_html',
                    __('Enter your address to view shipping options.', 'woocommerce')
                ) !!}
            @endif
        @elseif (!is_cart())
            {!! apply_filters(
                'woocommerce_no_shipping_available_html',
                __('There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.', 'woocommerce')
            ) !!}
        @else
            {!! apply_filters(
                'woocommerce_cart_no_shipping_available_html',
                sprintf(
                    esc_html__('No shipping options were found for %s.', 'woocommerce') . ' ',
                    '<strong>' . esc_html($formatted_destination) . '</strong>'
                ),
                $formatted_destination
            ) !!}
            @php $calculator_text = esc_html__('Enter a different address', 'woocommerce'); @endphp
        @endif

        @if ($show_package_details)
            <p class="woocommerce-shipping-contents">
                <small>{{ $package_details }}</small>
            </p>
        @endif

        @if ($show_shipping_calculator)
            {!! woocommerce_shipping_calculator($calculator_text) !!}
        @endif
    </td>
</tr>
