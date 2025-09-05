<?php

namespace App\WooCommerce;

/**
 * Product Loop Thumbnail
 */
add_action('woocommerce_before_shop_loop_item_title', function () {
    global $product;
    wc_get_template('loop/product-thumbnail.php', ['product' => $product]);
}, 10);

/**
 * Product Loop Title
 */
add_action('woocommerce_shop_loop_item_title', function () {
    global $product;
    wc_get_template('loop/product-title.php', ['product' => $product]);
}, 10);

/**
 * Product Loop Link Open
 */
add_action('woocommerce_before_shop_loop_item', function () {
    wc_get_template('loop/product-link-open.php');
}, 10);

/**
 * Product Loop Link Close
 */
add_action('woocommerce_after_shop_loop_item', function () {
    wc_get_template('loop/product-link-close.php');
}, 5);

/**
 * Before Shop Loop
 */
add_action('woocommerce_before_shop_loop', function () {
    wc_get_template('loop/before-shop-loop.blade.php');
}, 20);
