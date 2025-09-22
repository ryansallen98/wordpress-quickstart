<div class="flex flex-col gap-2">
  @if (!empty($subtotals))
  @foreach ($subtotals as $subtotal)
  <div>
    <div class="flex w-full flex-col">
      <div class="flex w-full items-center justify-between">
        <div class="text-lg">
          {!! $subtotal->label !!}
        </div>
        @if ($subtotal->isCoupon)
          <div class="flex items-center gap-1 text-lg font-bold">
            @if ($subtotal->value)
              {!! $subtotal->value !!}
            @endif

            <x-tooltip>
              <x-slot:trigger>
                <div class="flex h-full items-center justify-center">
                  <a class="woocommerce-remove-coupon btn btn-ghost h-auto w-auto rounded-full p-0"
                    href="{{ $subtotal->removeUrl }}" data-coupon="{{ $subtotal->sanitizedCode }}"
                    aria-label="{{ esc_attr(sprintf(__('Remove coupon %s', 'woocommerce'), $subtotal->code)) }}">
                    <span class="sr-only">
                      {{ esc_html(sprintf(__('Remove coupon %s', 'woocommerce'), $subtotal->code)) }}
                    </span>
                    <x-lucide-x aria-hidden="true" class="text-destructive" />
                  </a>
                </div>
                </x-slot>
                <x-slot:content>
                  {{ esc_html(sprintf(__('Remove coupon %s', 'woocommerce'), $subtotal->code)) }}
                  </x-slot>
            </x-tooltip>
          </div>
        @else
          <div class="text-lg font-bold">
            @if ($subtotal->prefix)
              <span class="font-normal">{!! $subtotal->prefix !!}</span>
            @endif

            @if ($subtotal->value)
              <span>{!! $subtotal->value !!}</span>
            @endif
          </div>
        @endif
      </div>
    </div>
    @if (!empty($subtotal->description))
      <p class="text-muted-foreground text-sm">
        {!! $subtotal->description !!}
      </p>
    @endif

    @if (!empty($subtotal->contents_html))
      <div class="text-muted-foreground text-xs mt-1">
        {!! $subtotal->contents_html !!}
      </div>
    @endif

    @if(!empty($subtotal->isShipping))
    @pushOnce('shipping_notes')
    <div class="text-foreground text-sm -mt-2">
      @php do_action('woocommerce_review_order_after_shipping'); @endphp
    </div>
    @endpushOnce
    @endif
  </div>
  @endforeach

  @stack('shipping_notes')

  @endif
</div>



<x-separator />

@if ((function_exists('is_checkout') && is_checkout() && !function_exists('is_checkout_pay_page')) || (function_exists('is_checkout') && is_checkout() && function_exists('is_checkout_pay_page') && !is_checkout_pay_page()))
  @php
    do_action('woocommerce_review_order_before_order_total');
  @endphp
@endif

<div class="flex w-full items-start justify-between">
  <div class="text-lg">
    {{ esc_html__('Order total', 'woocommerce') }}
  </div>
  <div class="text-xl font-bold">
    {!! $order_total !!}
  </div>
</div>

@if (function_exists('is_checkout') && is_checkout() && (!function_exists('is_checkout_pay_page') || !is_checkout_pay_page()))
  @php
    do_action('woocommerce_review_order_after_order_total');
  @endphp
@endif

<x-separator />