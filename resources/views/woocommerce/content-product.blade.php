@php
  defined('ABSPATH') || exit();

  global $product;

  // Validate product + visibility (match core behavior)
  if (! ($product instanceof WC_Product) || ! $product->is_visible()) {
    return;
  }
@endphp

<li <?php wc_product_class('flex flex-col items-start', $product); ?>>
  @php
    /**
     * Hook: woocommerce_before_shop_loop_item.
     *
     * @hooked woocommerce_template_loop_product_link_open - 10
     */
    do_action('woocommerce_before_shop_loop_item');
  @endphp

  <div class="overflow-hidden w-full h-fit rounded-lg shadow-md mb-4 relative">
    @php
      /**
       * Hook: woocommerce_before_shop_loop_item_title.
       *
       * @hooked woocommerce_show_product_loop_sale_flash - 10
       * @hooked woocommerce_template_loop_product_thumbnail - 10
       */
      do_action('woocommerce_before_shop_loop_item_title');
    @endphp
  </div>

  @php
    /**
     * Hook: woocommerce_shop_loop_item_title.
     *
     * @hooked woocommerce_template_loop_product_title - 10
     */
    do_action('woocommerce_shop_loop_item_title');

    /**
     * Hook: woocommerce_after_shop_loop_item_title.
     *
     * @hooked woocommerce_template_loop_rating - 5
     * @hooked woocommerce_template_loop_price - 10
     */
    do_action('woocommerce_after_shop_loop_item_title');

    /**
     * Hook: woocommerce_after_shop_loop_item.
     *
     * @hooked woocommerce_template_loop_product_link_close - 5
     * @hooked woocommerce_template_loop_add_to_cart - 10
     */
    do_action('woocommerce_after_shop_loop_item');
  @endphp
</li>
