@php
    defined('ABSPATH') || exit;
    $saved_methods = wc_get_customer_saved_methods_list(get_current_user_id());
    $has_methods = (bool) $saved_methods;
    $types = wc_get_account_payment_methods_types();
@endphp

@php do_action('woocommerce_before_account_payment_methods', $has_methods); @endphp

<div class="flex items-start justify-between mb-6">
    <h1 class="text-2xl font-bold">{!! $title !!}</h1>

    @if (WC()->payment_gateways->get_available_payment_gateways())
        <a class="btn btn-primary" href="{{ esc_url(wc_get_endpoint_url('add-payment-method')) }}">
            <x-lucide-credit-card />
            {{ esc_html__('Add payment method', 'woocommerce') }}
        </a>
    @endif
</div>

@if ($has_methods)
    <div class="overflow-x-auto border rounded-lg overflow-auto border-b-0 shadow-sm">
        <table class="table">
            <thead class="thead">
                <tr class="border-b border-border">
                    @foreach (wc_get_account_payment_methods_columns() as $column_id => $column_name)
                        <th
                            class="th woocommerce-PaymentMethod woocommerce-PaymentMethod--{{ esc_attr($column_id) }} payment-method-{{ esc_attr($column_id) }}">
                            <span class="nobr">{!! esc_html($column_name) !!}</span>
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach ($saved_methods as $type => $methods)
                    @foreach ($methods as $method)
                        <tr class="payment-method {{ !empty($method['is_default']) ? 'default-payment-method' : '' }}">
                            @foreach (wc_get_account_payment_methods_columns() as $column_id => $column_name)
                                @php
                                    $extra_class = $column_id === 'actions' ? 'text-right' : '';
                                @endphp

                                <td class="td {{ $extra_class }} woocommerce-PaymentMethod woocommerce-PaymentMethod--{{ esc_attr($column_id) }} payment-method-{{ esc_attr($column_id) }}"
                                    data-title="{{ esc_attr($column_name) }}">
                                    @php
                                        if (has_action('woocommerce_account_payment_methods_column_' . $column_id)) {
                                            do_action('woocommerce_account_payment_methods_column_' . $column_id, $method);
                                        } elseif ('method' === $column_id) {
                                            if (!empty($method['method']['last4'])) {
                                                // translators: 1: credit card type 2: last 4 digits
                                                echo sprintf(
                                                    esc_html__('%1$s ending in %2$s', 'woocommerce'),
                                                    esc_html(wc_get_credit_card_type_label($method['method']['brand'])),
                                                    esc_html($method['method']['last4'])
                                                );
                                            } else {
                                                echo esc_html(wc_get_credit_card_type_label($method['method']['brand']));
                                            }
                                        } elseif ('expires' === $column_id) {
                                            echo esc_html($method['expires']);
                                        } elseif ('actions' === $column_id) {
                                            foreach ($method['actions'] as $key => $action) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
                                                // Map Woo "button" to your styling
                                                echo '<a href="' . esc_url($action['url']) . '" class="btn btn-outline ' . sanitize_html_class($key) . '">'
                                                    . svg('lucide-trash')->toHtml()
                                                    . esc_html($action['name'])
                                                    . '</a>';
                                            }
                                        }
                                    @endphp
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <x-alert class="cursor-default">
        <x-lucide-info aria-hidden="true" />
        <x-alert.title>
            {!! esc_html__('Heads Up', 'wordpress-quickstart') !!}
        </x-alert.title>
        <x-alert.description>
            {!! esc_html__('No saved methods found.', 'woocommerce') !!}
        </x-alert.description>

        <x-alert.actions>
            <a class="btn btn-primary btn-sm" href="{{ esc_url(wc_get_endpoint_url('add-payment-method')) }}">
                <x-lucide-credit-card />
                {{ esc_html__('Add payment method', 'woocommerce') }}
            </a>
        </x-alert.actions>
    </x-alert>
@endif

@php do_action('woocommerce_after_account_payment_methods', $has_methods); @endphp