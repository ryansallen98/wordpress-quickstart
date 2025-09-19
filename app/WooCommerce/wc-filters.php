<?php

namespace App\WooCommerce;

use DOMDocument;
use DOMXPath;

/**
 * Disable WooCommerce default stylesheets
 */
add_filter('woocommerce_enqueue_styles', function ($styles) {
  // Remove all WooCommerce styles
  return [];
});

/**
 * Customize WooCommerce Mini Cart buttons
 */
add_action('init', function () {
  // Remove default buttons
  remove_action('woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10);
  remove_action('woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_proceed_to_checkout', 20);

  // Add custom buttons back
  add_action('woocommerce_widget_shopping_cart_buttons', function () {
    // Only show "View cart" if NOT on the cart page
    if (!is_cart()) {
      $cart_url = wc_get_cart_url();
      echo '<a href="' . esc_url($cart_url) . '" class="btn btn-outline btn-lg flex-1">'
        . svg('lucide-shopping-cart')->toHtml()
        . esc_html__('View cart', 'woocommerce')
        . '</a>';
    }

    // Always show "Checkout"
    $checkout_url = wc_get_checkout_url();
    echo '<a href="' . esc_url($checkout_url) . '" class="btn btn-primary btn-lg flex-1">'
      . svg('lucide-credit-card')->toHtml()
      . esc_html__('Checkout', 'woocommerce')
      . '</a>';
  }, 10);
});