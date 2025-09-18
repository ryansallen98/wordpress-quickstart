@php
    defined('ABSPATH') || exit;
    $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
@endphp

@if ($available_gateways)
    <div>
        <div>
            <div class="pb-4 border-b border-border flex items-start justify-between">

                <div>
                    <h2 class="text-2xl font-semibold tracking-tight text-card-foreground mb-2">
                        {{ esc_html__('Add a payment method', 'woocommerce') }}
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        {{ esc_html__('Choose a payment method and enter its details to save it to your account.', 'woocommerce') }}
                    </p>
                </div>

                <a href="{{ esc_url(wc_get_endpoint_url('payment-methods')) }}" class="btn btn-outline">
                    <x-lucide-chevron-left />
                    {{ esc_html__('Back to methods', 'woocommerce') }}
                </a>
            </div>

            <form id="add_payment_method" method="post" class="space-y-6 pt-6">
                <div id="payment" class="woocommerce-Payment">
                    <ul class="woocommerce-PaymentMethods payment_methods methods space-y-4">
                        @php
                            // Chosen method
                            if (count($available_gateways)) {
                                current($available_gateways)->set_current();
                            }
                        @endphp

                        @foreach ($available_gateways as $gateway)
                            <li
                                class="woocommerce-PaymentMethod woocommerce-PaymentMethod--{{ esc_attr($gateway->id) }} payment_method_{{ esc_attr($gateway->id) }}">
                                <div class="flex items-start gap-3">
                                    <input id="payment_method_{{ esc_attr($gateway->id) }}" type="radio"
                                        class="input-radio mt-1" name="payment_method" value="{{ esc_attr($gateway->id) }}"
                                        @checked($gateway->chosen) />
                                    <label for="payment_method_{{ esc_attr($gateway->id) }}"
                                        class="cursor-pointer text-sm font-medium text-foreground flex items-center gap-2">
                                        {!! wp_kses_post($gateway->get_title()) !!}
                                        {!! wp_kses_post($gateway->get_icon()) !!}
                                    </label>
                                </div>

                                @if ($gateway->has_fields() || $gateway->get_description())
                                    {{-- Keep WooCommerce’s expected structure & classes so its JS can toggle this --}}
                                    <div class="woocommerce-PaymentBox woocommerce-PaymentBox--{{ esc_attr($gateway->id) }} payment_box payment_method_{{ esc_attr($gateway->id) }} mt-4 rounded-md border border-border bg-muted/30 p-4"
                                        style="display: none;">
                                        @php $gateway->payment_fields(); @endphp
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    @php do_action('woocommerce_add_payment_method_form_bottom'); @endphp

                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        @php wp_nonce_field('woocommerce-add-payment-method', 'woocommerce-add-payment-method-nonce'); @endphp
                        <button type="submit" class="{{ $btn }} woocommerce-Button woocommerce-Button--alt button alt"
                            id="place_order" value="{{ esc_attr__('Add payment method', 'woocommerce') }}">
                            {{ esc_html__('Add payment method', 'woocommerce') }}
                        </button>
                        <input type="hidden" name="woocommerce_add_payment_method" id="woocommerce_add_payment_method"
                            value="1" />
                    </div>
                </div>
            </form>
        </div>
    </div>
@else
    @php wc_print_notice(esc_html__('New payment methods can only be added during checkout. Please contact us if you require assistance.', 'woocommerce'), 'notice'); @endphp
    <div class="mt-4">
        <a href="{{ esc_url(wc_get_page_permalink('shop')) }}" class="{{ $btnGhost }}">
            {{ esc_html__('Go to shop', 'woocommerce') }}
        </a>
    </div>
@endif