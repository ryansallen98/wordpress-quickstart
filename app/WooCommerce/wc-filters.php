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

// /**
//  * Force disable AJAX add-to-cart on archives
//  */
// add_filter('pre_option_woocommerce_enable_ajax_add_to_cart', function ($value) {
//     return 'no';
// });

// /**
//  * Remove the AJAX add-to-cart checkbox from WooCommerce settings
//  */
// add_filter('woocommerce_product_settings', function ($settings) {
//     foreach ($settings as $key => $setting) {
//         if (isset($setting['id']) && 'woocommerce_enable_ajax_add_to_cart' === $setting['id']) {
//             unset($settings[$key]);
//         }
//     }
//     return $settings;
// });


