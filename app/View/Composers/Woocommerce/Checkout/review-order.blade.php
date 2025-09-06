{{-- resources/views/woocommerce/checkout/review-order.blade.php --}}
@php
  defined('ABSPATH') || exit();
@endphp

<div
  class="shop_table woocommerce-checkout-review-order-table mb-4 flex flex-col gap-4"
>
  <div>
    @php
      do_action('woocommerce_review_order_before_cart_contents');
    @endphp

    <div class="flex flex-col gap-4">
      @foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item)
        @php
          $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
          $visible = apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key);
          $quantity = $cart_item['quantity'] ?? 0;
          $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product ? $_product->get_image('woocommerce_thumbnail') : '', $cart_item, $cart_item_key);

          // Clean product name (parent name for variations)
          if ($_product && $_product->is_type('variation')) {
            $parent = wc_get_product($_product->get_parent_id());
            $product_name = $parent ? $parent->get_name() : $_product->get_name();
          } else {
            $product_name = $_product ? $_product->get_name() : '';
          }

          // Build a plain array of attributes to render in Blade (no HTML strings)
          $attributes = [];

          // 1) Prefer WooCommerce item data (covers variations + add-ons, etc.)
          $item_data = function_exists('wc_get_item_data') ? wc_get_item_data($cart_item) : [];

          if (! empty($item_data)) {
            foreach ($item_data as $data) {
              $label = isset($data['key']) ? wc_clean(wp_strip_all_tags($data['key'])) : '';
              $value = isset($data['display']) ? wc_clean(wp_strip_all_tags($data['display'])) : (isset($data['value']) ? wc_clean(wp_strip_all_tags($data['value'])) : '');
              if ($label !== '' && $value !== '') {
                $attributes[] = ['label' => $label, 'value' => $value];
              }
            }
          }

          // 2) Fallback: build attributes from the cart item's variation array (if needed)
          if (empty($attributes) && ! empty($cart_item['variation'])) {
            foreach ($cart_item['variation'] as $attr_key => $attr_value) {
              if (! $attr_value) {
                continue;
              }

              // Normalize key like 'attribute_pa_color' => 'pa_color'
              $taxonomy = str_replace('attribute_', '', $attr_key);

              // Human label
              $label = function_exists('wc_attribute_label') ? wc_attribute_label($taxonomy, $_product) : ucwords(str_replace(['pa_', '_', '-'], ['', ' ', ' '], $taxonomy));

              // Human value (term name if taxonomy, raw if custom)
              if (taxonomy_exists($taxonomy)) {
                $term = get_term_by('slug', $attr_value, $taxonomy);
                $value = $term && ! is_wp_error($term) ? $term->name : wc_clean($attr_value);
              } else {
                $value = wc_clean($attr_value);
              }

              $attributes[] = ['label' => $label, 'value' => $value];
            }
          }
        @endphp

        @if ($_product && $_product->exists() && $quantity > 0 && $visible)
          <div class="flex items-start justify-between gap-4">
            <!-- LEFT: image with quantity badge -->
            <div class="relative hidden sm:block">
              <div
                class="w-10 h-10 sm:w-14 sm:h-14 lg:h-20 lg:w-20 shrink-0 overflow-hidden rounded-lg shadow-md bg-muted"
              >
                {!! wp_kses_post($thumbnail) !!}
              </div>
              <span
                class="bg-primary text-primary-foreground absolute -top-2 -left-2 inline-flex items-center justify-center rounded-full px-2 py-1 text-xs font-semibold shadow-sm"
              >
                {{ $quantity }}
              </span>
            </div>

            <!-- MIDDLE: title + attributes/description -->
            <div class="min-w-0 flex-1">
              {{-- Product name only, no attributes --}}
              <div class="leading-snug font-medium">
                {{ $product_name }}
              </div>

              {{-- Attributes as a proper list (Blade-only markup) --}}
              @if (! empty($attributes))
                <ul
                  class="text-muted-foreground mt-1 list-inside list-disc space-y-0.5 text-sm font-medium"
                >
                  @foreach ($attributes as $attr)
                    <li>
                      <span class="text-muted-foreground">
                        {{ $attr['label'] }}:
                      </span>
                      {{ $attr['value'] }}
                    </li>
                  @endforeach
                </ul>
              @else
                {{-- Fallback short description --}}
                <div class="text-muted-foreground mt-1 text-sm">
                  {!! wp_kses_post(wp_trim_words($_product->get_short_description(), 12, '…')) !!}
                </div>
              @endif
            </div>

            <!-- RIGHT: line subtotal -->
            <div class="shrink-0 text-right text-lg font-bold">
              {!!
                apply_filters(
                  'woocommerce_cart_item_subtotal',
                  WC()->cart->get_product_subtotal($_product, $quantity),
                  $cart_item,
                  $cart_item_key,
                )
              !!}

              @if ($quantity > 1)
                <div class="text-muted-foreground mt-1 text-sm font-normal">
                  <div>
                    {{ $quantity }} × {!! wc_price($_product->get_price()) !!}
                  </div>
                </div>
              @endif
            </div>
          </div>
        @endif
      @endforeach
    </div>
  </div>

  <x-separator />

  @php
    ob_start();
    woocommerce_checkout_coupon_form();
    $coupon_form = trim(ob_get_clean());
  @endphp

  @if ($coupon_form)
    {!! $coupon_form !!}
    <x-separator />
  @endif

  <div class="flex w-full flex-col gap-2">
    <div class="flex w-full items-center justify-between">
      <div class="text-lg">
        @php
          esc_html_e('Subtotal', 'woocommerce');
        @endphp
      </div>
      <div class="text-lg font-bold">
        @php
          wc_cart_totals_subtotal_html();
        @endphp
      </div>
    </div>

    @foreach (WC()->cart->get_coupons() as $code => $coupon)
      @php
        // 1) Label (or build your own string if you want)
        ob_start();
        wc_cart_totals_coupon_label($coupon);
        $labelHtml = trim(ob_get_clean());

        // 2) Correct amount (match Woo's logic)
        $discount = WC()->cart->get_coupon_discount_amount($code); // base discount
        $discount_tax = WC()->cart->get_coupon_discount_tax_amount($code); // tax saved by the discount

        if (WC()->cart->display_prices_including_tax()) {
          $display_amount = $discount + $discount_tax; // show discount incl. its tax
        } else {
          $display_amount = $discount; // show discount excl. tax
        }

        $amountHtml = '-' . wc_price($display_amount);
        // Let extensions modify if needed (keeps parity with Woo)
        $amountHtml = apply_filters('woocommerce_coupon_discount_amount_html', $amountHtml, $coupon);

        // 3) Remove URL (helper if available, else fallback)
        if (function_exists('wc_get_cart_remove_coupon_url')) {
          $removeUrl = esc_url(wc_get_cart_remove_coupon_url($code));
        } else {
          $removeUrl = esc_url(wp_nonce_url(add_query_arg(['remove_coupon' => rawurlencode($code)], wc_get_cart_url()), 'woocommerce-cart'));
        }

        $sanitizedCode = esc_attr(sanitize_title($coupon->get_code()));
      @endphp

      <div
        class="coupon-{{ $sanitizedCode }} flex w-full items-center justify-between"
      >
        <div class="text-lg">{!! $labelHtml !!}</div>

        <div class="flex items-center gap-1 text-lg font-bold">
          {!! $amountHtml !!}
          <x-tooltip>
            <x-slot:trigger>
              <div class="flex h-full items-center justify-center">
                <x-button
                  variant="ghost"
                  size="icon"
                  data-coupon="{{ $sanitizedCode }}"
                  class="h-auto w-auto rounded-full p-0 woocommerce-remove-coupon"
                  aria-label="{{ esc_attr(sprintf(__('Remove coupon %s', 'woocommerce'), $coupon->get_code())) }}"
                  href="{{ $removeUrl }}"
                >
                  <span class="sr-only">
                    {{ esc_attr(sprintf(__('Remove coupon %s', 'woocommerce'), $coupon->get_code())) }}
                  </span>
                  <x-lucide-x />
                </x-button>
              </div>
            </x-slot>
            <x-slot:content>
              {{ esc_attr(sprintf(__('Remove coupon %s', 'woocommerce'), $coupon->get_code())) }}
            </x-slot>
          </x-tooltip>
        </div>
      </div>
    @endforeach

    @if (WC()->cart->needs_shipping() && WC()->cart->show_shipping())
      @php
        do_action('woocommerce_review_order_before_shipping');
        ob_start();
        wc_cart_totals_shipping_html();
        $shipping_html = trim(ob_get_clean());

        // Remove the default "Shipping" label/heading
        $shipping_html = preg_replace('/<th[^>]*>.*?<\/th>/i', '', $shipping_html);
      @endphp

      @if (! empty($shipping_html))
        <div class="flex w-full items-start justify-between">
          <div class="text-lg">
            {{ __('Shipping', 'woocommerce') }}
          </div>
          <div class="max-w-[60%] text-right text-lg font-bold">
            {!! $shipping_html !!}
          </div>
        </div>
      @endif

      @php
        do_action('woocommerce_review_order_after_shipping');
      @endphp
    @endif

    @foreach (WC()->cart->get_fees() as $fee)
      <div class="flex w-full items-start justify-between">
        <div class="text-lg">
          {{ esc_html($fee->name) }}
        </div>
        <div class="text-lg font-bold">
          @php
            wc_cart_totals_fee_html($fee);
          @endphp
        </div>
      </div>
    @endforeach

    @if (wc_tax_enabled() && ! WC()->cart->display_prices_including_tax())
      @if ('itemized' === get_option('woocommerce_tax_total_display'))
        @foreach (WC()->cart->get_tax_totals() as $code => $tax)
          <div class="flex w-full items-start justify-between">
            <div class="text-lg">
              {{ esc_html($tax->label) }}
            </div>
            <div class="text-lg font-bold">
              {!! wp_kses_post($tax->formatted_amount) !!}
            </div>
          </div>
        @endforeach
      @else
        <div class="flex w-full items-start justify-between">
          <div class="text-lg">
            {{ esc_html(WC()->countries->tax_or_vat()) }}
          </div>
          <div class="text-lg font-bold">
            @php
              wc_cart_totals_taxes_total_html();
            @endphp
          </div>
        </div>
      @endif
    @endif
  </div>

  @php
    do_action('woocommerce_review_order_before_order_total');
  @endphp

  <x-separator />

  <div class="flex w-full items-start justify-between">
    <div class="text-lg">
      {{ esc_html__('Order total', 'woocommerce') }}
    </div>
    <div class="text-xl font-bold">
      @php
        wc_cart_totals_order_total_html();
      @endphp
    </div>
  </div>

  @php
    do_action('woocommerce_review_order_after_order_total');
  @endphp

  <x-separator />
</div>
