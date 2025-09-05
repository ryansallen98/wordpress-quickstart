<?php
/**
 * WooCommerce: Orderby (Blade)
 * Mirrors templates/loop/orderby.php but renders a custom Blade select.
 *
 * Expects (from WooCommerce):
 * - $catalog_orderby_options : array  id => label
 * - $orderby                 : string current value
 * - $use_label               : bool   show "Sort by" label
 */

if (! defined('ABSPATH')) {
  exit();
}

$idSuffix = wp_unique_id();
$selectId = "woocommerce-orderby-{$idSuffix}";
?>

<form
  method="get"
  x-data="{
    orderby: @js($orderby),
    submitForm() {
      $el.requestSubmit?.() || $el.submit()
    },
  }"
>
  @if (! empty($use_label))
    <label for="{{ $selectId }}">{{ __('Sort by', 'woocommerce') }}</label>
  @endif

  <x-form.select
    name="orderby"
    :value="$orderby"
    id="{{ $selectId }}"
    @change="submitForm()"
    aria-label="{{ empty($use_label) ? esc_attr__('Shop order', 'woocommerce') : '' }}"
  >
    <x-form.select.trigger>
      <x-form.select.value placeholder="{{ __('Order By', 'woocommerce') }}" />
    </x-form.select.trigger>

    <x-form.select.content>
      <x-form.select.label>
        {{ __('Sort options', 'woocommerce') }}
      </x-form.select.label>

      @foreach ($catalog_orderby_options as $id => $name)
        <x-form.select.item
          value="{{ $id }}"
          :selected="$orderby === $id"
          label="{{ $name }}"
        />
      @endforeach
    </x-form.select.content>
  </x-form.select>

  {{-- Reset page to 1 like core template --}}
  <input type="hidden" name="paged" value="1" />

  {{-- Preserve existing query args except these --}}

  <?php wc_query_string_form_fields(null, [
    'orderby',
    'submit',
    'paged',
    'product-page',
  ]); ?>

  {{-- Progressive enhancement: visible only if JS fails / user wants manual submit --}}
  <noscript>
    <x-button type="submit">
      {{ __('Apply', 'woocommerce') }}
    </x-button>
  </noscript>
</form>
