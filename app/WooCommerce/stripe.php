<?php

namespace App\WooCommerce;


/**
 * Use default Stripe Theme
 */
add_filter('wc_stripe_upe_params', function ($p) {
    $p['appearance'] = (object) ['theme' => 'stripe'];
    return $p;
});

// // Clear cached appearance so changes apply immediately.
add_action('init', function () {
    delete_transient('wc_stripe_appearance');
});