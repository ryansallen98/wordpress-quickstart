@php
  /** @var \WC_Order $order */
  $b = $order->get_address('billing');   // array of fields
  $s = $order->get_address('shipping');  // array of fields

  // Helper to render a single address block with custom HTML
  $renderAddress = function(array $a): string {
    $lines = [];

    // Name or company+name
    $name = trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? ''));
    if (!empty($a['company'])) {
      $lines[] = esc_html($a['company']);
    }
    if ($name !== '') {
      $lines[] = esc_html($name);
    }

    // Street
    foreach (['address_1','address_2'] as $k) {
      if (!empty($a[$k])) $lines[] = esc_html($a[$k]);
    }

    // City / State / Postcode
    $cityLine = trim(
      implode(' ', array_filter([
        $a['city']    ?? '',
        $a['state']   ?? '',
        $a['postcode']?? '',
      ]))
    );
    if ($cityLine !== '') $lines[] = esc_html($cityLine);

    // Country (optional; comment out if you don't want it)
    if (!empty($a['country'])) {
      $country_name = WC()->countries->countries[ $a['country'] ] ?? $a['country'];
      $lines[] = esc_html($country_name);
    }

    // Join as <div> lines (use <p> if you prefer)
    return implode('', array_map(fn($l) => "<div>{$l}</div>", $lines));
  };
@endphp

<div class="flex flex-col lg:flex-row gap-4 lg:gap-8">
  {{-- Billing --}}
  <section aria-labelledby="addr-billing-title" class="space-y-2">
    <h3 id="addr-billing-title" class="text-xl font-semibold">
      {{ __('Billing address', 'woocommerce') }}
    </h3>

    <div class="text-base leading-6">
      {!! $renderAddress($b) ?: '<div>' . esc_html__('N/A', 'woocommerce') . '</div>' !!}
    </div>
  </section>

  {{-- Shipping (fallback to billing if store doesn’t ship to different address) --}}
  <section aria-labelledby="addr-shipping-title" class="space-y-2">
    <h3 id="addr-shipping-title" class="text-xl font-semibold">
      {{ __('Shipping address', 'woocommerce') }}
    </h3>

    @php
      // Some orders won't have a separate shipping address (virtual or same as billing)
      $shippingHtml = $renderAddress($s);
      if ($shippingHtml === '' && wc_ship_to_billing_address_only()) {
        $shippingHtml = $renderAddress($b);
      }
    @endphp

    <div class="text-base leading-6">
      {!! $shippingHtml ?: '<div>' . esc_html__('N/A', 'woocommerce') . '</div>' !!}
    </div>
  </section>
</div>