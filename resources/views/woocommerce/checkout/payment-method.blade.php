@php
  defined('ABSPATH') || exit();
  /** @var WC_Payment_Gateway $gateway */
  $radio_id = 'payment_method_' . esc_attr($gateway->id);
  $panel_id = 'payment_box_' . esc_attr($gateway->id);
@endphp

<li
  class="wc_payment_method payment_method_{{ esc_attr($gateway->id) }} list-none
         [&:has(>input:checked):not(:only-child)_.checkmark]:flex"
>
  <!-- Native Woo radio, visually hidden but focusable -->
  <input
    id="{{ $radio_id }}"
    type="radio"
    class="input-radio peer sr-only"
    name="payment_method"
    value="{{ esc_attr($gateway->id) }}"
    @checked($gateway->chosen)
    data-order_button_text="{{ esc_attr($gateway->order_button_text) }}"
    aria-controls="{{ $panel_id }}"
  />

  <!-- Label reacts to input focus/checked -->
  <label
    for="{{ $radio_id }}"
    class="bg-card hover:bg-accent/50 peer-focus-visible:ring-primary/50 group flex cursor-pointer items-start gap-3 rounded-md border p-4 transition peer-focus-visible:ring-2 focus:outline-none"
  >
    <span class="flex flex-1 items-start gap-3">
      <span class="h-5 w-5 min-w-5 pt-0.5">
        @php
          $icon_classes = 'h-5 w-5';
        @endphp

        @switch($gateway->id)
          @case('bacs')
            <x-lucide-banknote class="{{ $icon_classes }}" />

            @break
          @case('cod')
            <x-lucide-package class="{{ $icon_classes }}" />

            @break
          @case('cheque')
            <x-lucide-file-text class="{{ $icon_classes }}" />

            @break
          @case('stripe')
            <x-lucide-credit-card class="{{ $icon_classes }}" />

            @break
          @case('ppcp-gateway')
            <x-lucide-wallet class="{{ $icon_classes }}" />

            @break
        @endswitch
      </span>

      <span>
        <h3 class="leading-tight font-medium">{{ $gateway->get_title() }}</h3>
        @if (! $gateway->has_fields() && $gateway->get_description())
          <span class="sr-only">
            {{ wp_strip_all_tags($gateway->get_description()) }}
          </span>
        @endif
      </span>
    </span>

    <!-- Checkmark shows when radio is checked -->
    <span aria-hidden="true" class="checkmark hidden">
      <x-lucide-check class="text-muted-foreground/50 h-6 w-6" />
    </span>
  </label>

  @if ($gateway->has_fields() || $gateway->get_description())
    <div
      id="{{ $panel_id }}"
      class="payment_box payment_method_{{ esc_attr($gateway->id) }}"
      role="region"
      aria-live="polite"
      aria-labelledby="{{ $radio_id }}"
    >
      <div class="bg-muted mt-2 rounded-md border-2 border-dashed p-4">
        @php
          $gateway->payment_fields();
        @endphp
      </div>
    </div>
  @endif
</li>
