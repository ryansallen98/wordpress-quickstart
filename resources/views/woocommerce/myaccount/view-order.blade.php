@php
    /** @var \WC_Order $order */
    $notes = $order->get_customer_order_notes();
@endphp

<div class="mb-6">
    <div class="flex items-center mb-2 gap-2">
        <a class="btn btn-ghost btn-icon" href="{{ esc_url(wc_get_endpoint_url('orders')) }}">
            <x-lucide-chevron-left aria-hidden="true" />
            <span class="sr-only">{!! __('Back to orders', 'woocommerce') !!}</span>
        </a>
        <h1 class="text-2xl font-bold">{!! __('Order ' . $order->get_order_number(), 'woocommerce') !!}</h1>
    </div>

    <p class="text-sm">
        {!! apply_filters(
    'woocommerce_order_details_status',
    sprintf(
        /* translators: 1: order number 2: order date 3: order status */
        esc_html__('Order #%1$s was placed on %2$s and is currently %3$s.', 'woocommerce'),
        '<strong class="order-number">' . $order->get_order_number() . '</strong>',
        '<strong class="order-date">' . wc_format_datetime($order->get_date_created()) . '</strong>',
        '<strong class="order-status">' . wc_get_order_status_name($order->get_status()) . '</strong>'
    ),
    $order
) !!}
    </p>
</div>

@if ($notes)
    <h2 class="mb-2 text-xl font-bold">{{ __('Order updates', 'woocommerce') }}</h2>
    <div class="mb-6">
        <ol class="woocommerce-OrderUpdates commentlist notes flex flex-col gap-2">
            @foreach ($notes as $note)
                <li class="woocommerce-OrderUpdate comment note">
                    <div class="woocommerce-OrderUpdate-inner comment_container">
                        <div class="woocommerce-OrderUpdate-text comment-text">
                            <p class="woocommerce-OrderUpdate-meta meta text-sm text-muted-foreground">
                                {!! date_i18n(esc_html__('l jS \\o\\f F Y, h:ia', 'woocommerce'), strtotime($note->comment_date)) !!}
                            </p>
                            <div class="woocommerce-OrderUpdate-description description">
                                {!! wpautop(wptexturize($note->comment_content)) !!}
                            </div>
                            <div class="clear"></div>
                        </div>
                        <div class="clear"></div>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
@endif

@php do_action('woocommerce_view_order', $order_id); @endphp