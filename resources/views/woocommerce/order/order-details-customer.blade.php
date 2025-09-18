{{-- resources/views/woocommerce/order/order-details-customer.blade.php --}}

@php
    /** @var \WC_Order $order */
    $show_shipping = ! wc_ship_to_billing_address_only() && $order->needs_shipping_address();
    $na = esc_html__( 'N/A', 'woocommerce' );
@endphp

<section class="woocommerce-customer-details mt-6">
    @if ($show_shipping)
        <div class="grid gap-6 md:grid-cols-2">
            {{-- Billing --}}
            <div class="bg-card border border-border rounded-lg shadow-sm">
                <div class="p-6">
                    <h2 class="text-base font-semibold tracking-tight text-card-foreground">
                        {{ __('Billing address', 'woocommerce') }}
                    </h2>

                    <div class="mt-4 space-y-3 text-sm leading-6 text-muted-foreground not-prose">
                        <address class="not-italic">
                            {!! wp_kses_post($order->get_formatted_billing_address($na)) !!}
                        </address>

                        @if ($order->get_billing_phone())
                            <p class="flex items-center gap-2">
                                @if (class_exists('Illuminate\\Support\\Facades\\Blade')) {{-- lucide optional --}}
                                    <x-lucide-phone class="h-4 w-4 text-muted-foreground" aria-hidden="true"/>
                                @endif
                                <span>{{ esc_html($order->get_billing_phone()) }}</span>
                            </p>
                        @endif

                        @if ($order->get_billing_email())
                            <p class="flex items-center gap-2 break-all">
                                @if (class_exists('Illuminate\\Support\\Facades\\Blade'))
                                    <x-lucide-mail class="h-4 w-4 text-muted-foreground" aria-hidden="true"/>
                                @endif
                                <span>{{ esc_html($order->get_billing_email()) }}</span>
                            </p>
                        @endif

                        @php
                            /**
                             * After billing address hook.
                             *
                             * @since 8.7.0
                             */
                            do_action('woocommerce_order_details_after_customer_address', 'billing', $order);
                        @endphp
                    </div>
                </div>
            </div>

            {{-- Shipping --}}
            <div class="bg-card border border-border rounded-lg shadow-sm">
                <div class="p-6">
                    <h2 class="text-base font-semibold tracking-tight text-card-foreground">
                        {{ __('Shipping address', 'woocommerce') }}
                    </h2>

                    <div class="mt-4 space-y-3 text-sm leading-6 text-muted-foreground not-prose">
                        <address class="not-italic">
                            {!! wp_kses_post($order->get_formatted_shipping_address($na)) !!}
                        </address>

                        @if ($order->get_shipping_phone())
                            <p class="flex items-center gap-2">
                                @if (class_exists('Illuminate\\Support\\Facades\\Blade'))
                                    <x-lucide-phone class="h-4 w-4 text-muted-foreground" aria-hidden="true"/>
                                @endif
                                <span>{{ esc_html($order->get_shipping_phone()) }}</span>
                            </p>
                        @endif

                        @php
                            /**
                             * After shipping address hook.
                             *
                             * @since 8.7.0
                             */
                            do_action('woocommerce_order_details_after_customer_address', 'shipping', $order);
                        @endphp
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- Single column (billing only) --}}
        <div class="bg-card border border-border rounded-2xl shadow-sm">
            <div class="p-6">
                <h2 class="text-base font-semibold tracking-tight text-card-foreground">
                    {{ __('Billing address', 'woocommerce') }}
                </h2>

                <div class="mt-4 space-y-3 text-sm leading-6 text-muted-foreground not-prose">
                    <address class="not-italic">
                        {!! wp_kses_post($order->get_formatted_billing_address($na)) !!}
                    </address>

                    @if ($order->get_billing_phone())
                        <p class="flex items-center gap-2">
                            @if (class_exists('Illuminate\\Support\\Facades\\Blade'))
                                <x-lucide-phone class="h-4 w-4 text-muted-foreground" aria-hidden="true"/>
                            @endif
                            <span>{{ esc_html($order->get_billing_phone()) }}</span>
                        </p>
                    @endif

                    @if ($order->get_billing_email())
                        <p class="flex items-center gap-2 break-all">
                            @if (class_exists('Illuminate\\Support\\Facades\\Blade'))
                                <x-lucide-mail class="h-4 w-4 text-muted-foreground" aria-hidden="true"/>
                            @endif
                            <span>{{ esc_html($order->get_billing_email()) }}</span>
                        </p>
                    @endif

                    @php
                        do_action('woocommerce_order_details_after_customer_address', 'billing', $order);
                    @endphp
                </div>
            </div>
        </div>
    @endif

    {{-- Footer hook --}}
    @php do_action('woocommerce_order_details_after_customer_details', $order); @endphp
</section>