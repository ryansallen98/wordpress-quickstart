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

add_filter( 'loop_shop_columns', function() {
    return 6; // change to 2, 3, 4, etc.
}, 20 );

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
      . svg('heroicon-s-lock-closed')->toHtml()
      . esc_html__('Checkout', 'woocommerce')
      . '</a>';
  }, 10);
});


/**
 * Show parent product title in cart (hide variation attrs in the title).
 * Variation attributes will still appear in the cart-item-data block below.
 */
add_filter( 'woocommerce_cart_item_name', function( $name, $cart_item, $cart_item_key ) {
    if ( empty( $cart_item['data'] ) || ! is_a( $cart_item['data'], 'WC_Product' ) ) {
        return $name;
    }

    $product = $cart_item['data'];

    // Only adjust for variations.
    if ( $product->is_type( 'variation' ) ) {
        $parent_id   = $product->get_parent_id();
        $parent      = $parent_id ? wc_get_product( $parent_id ) : null;
        $parent_name = $parent ? $parent->get_name() : $product->get_name(); // Fallback.

        // Respect WooCommerce's "link to product" behavior in the cart.
        $product_permalink = apply_filters(
            'woocommerce_cart_item_permalink',
            $product->is_visible() ? $product->get_permalink( $cart_item ) : '',
            $cart_item,
            $cart_item_key
        );

        // Build the replacement name: parent title only (no attributes).
        if ( $product_permalink ) {
            $name = sprintf(
                '<a href="%s" class="no-underline! hover:underline!">%s</a>',
                esc_url( $product_permalink ),
                wp_kses_post( $parent_name )
            );
        } else {
            $name = wp_kses_post( $parent_name );
        }
    }

    return $name;
}, 10, 3 );


/**
 * Ensure variation attributes are in cart item data (not in the title),
 * without nuking any existing item data from other plugins.
 */
add_filter( 'woocommerce_get_item_data', function ( $item_data, $cart_item ) {

    // If there are no variation attributes, keep whatever is already there.
    if ( empty( $cart_item['variation'] ) || empty( $cart_item['data'] ) ) {
        return $item_data;
    }

    $product   = $cart_item['data'];
    $new_rows  = [];

    foreach ( $cart_item['variation'] as $attr_key => $attr_value ) {
        // Normalize key and make a readable label
        $tax_or_name = str_replace( 'attribute_', '', $attr_key );
        $label       = wc_attribute_label( $tax_or_name, $product );

        // Get readable value (convert taxonomy slugs, prettify custom attrs)
        if ( taxonomy_exists( $tax_or_name ) ) {
            $term  = get_term_by( 'slug', $attr_value, $tax_or_name );
            $value = $term && ! is_wp_error( $term ) ? $term->name : $attr_value;
        } else {
            // Custom product attribute (not a taxonomy) — prettify sluggy text.
            $value = wc_clean( ucwords( str_replace( array( '-', '_' ), ' ', (string) $attr_value ) ) );
        }

        if ( $value === '' ) {
            continue;
        }

        // Remove any existing row with the same label to avoid duplicates.
        $item_data = array_values( array_filter(
            $item_data,
            static function ( $row ) use ( $label ) {
                return ! isset( $row['key'] ) || $row['key'] !== $label;
            }
        ) );

        $new_rows[] = array(
            'key'     => $label,
            'value'   => $value,
            'display' => $value,
        );
    }

    // Append our rows after everything else that’s already there.
    return array_merge( $item_data, $new_rows );

}, PHP_INT_MAX, 2 ); // Run as late as possible so we don't wipe earlier data.


// Re-add cross-sells to the cart page (they were removed in wc-template-hooks.php)
add_action( 'woocommerce_after_cart_table', 'woocommerce_cross_sell_display' );