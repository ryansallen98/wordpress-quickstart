<?php
/**
 * Product Cross-sell Offer (Theme-side, Sage)
 *
 * - Settings: Adds "Product cross-sell page" picker in WooCommerce → Settings → Advanced.
 * - Pages list: Adds a page state label for the configured page.
 * - Shortcode: [wc_product_cross_sell_offer] loads theme template.
 * - Redirect: After successful add-to-cart, if product has cross-sells, redirect there.
 *             (defers redirect to template_redirect so cart cookies persist)
 */

namespace App\WooCommerce;

if (!defined('ABSPATH')) { exit; }

/* ====================================================================================
 * SETTINGS: "Product cross-sell page"
 * ================================================================================== */

add_filter('woocommerce_get_settings_advanced', function (array $settings) {
    $insert_at = null;
    foreach ($settings as $i => $field) {
        if (!empty($field['id']) && $field['id'] === 'woocommerce_myaccount_page_id') {
            $insert_at = $i + 1; break;
        }
    }

    $field = [
        'title'    => __('Product cross-sell page', 'sage'),
        'desc'     => __('Select the page that shows product cross-sell offers. Add the shortcode [wc_product_cross_sell_offer] to that page.', 'sage'),
        'id'       => 'woocommerce_product_cross_sell_page_id',
        'type'     => 'single_select_page',
        'default'  => '',
        'class'    => 'wc-enhanced-select-nostd',
        'css'      => 'min-width:300px;',
        'desc_tip' => true,
        'autoload' => false,
        'args'     => [
            'exclude' => array_filter([
                (int) get_option('woocommerce_cart_page_id'),
                (int) get_option('woocommerce_checkout_page_id'),
                (int) get_option('woocommerce_myaccount_page_id'),
                (int) get_option('woocommerce_terms_page_id'),
            ]),
        ],
    ];

    if ($insert_at !== null) {
        array_splice($settings, $insert_at, 0, [$field]);
        return $settings;
    }
    foreach ($settings as $i => $s) {
        if (!empty($s['id']) && $s['id'] === 'advanced_page_options_end') {
            array_splice($settings, $i, 0, [$field]);
            return $settings;
        }
    }
    $settings[] = $field;
    return $settings;
}, 10);

add_filter('display_post_states', function ($states, $post) {
    if ('page' !== $post->post_type) return $states;

    $page_id = (int) get_option('woocommerce_product_cross_sell_page_id');
    if ($page_id > 0 && (int) $post->ID === $page_id) {
        $label = __('Product Cross-sell Page', 'sage');
        $states['wc_product_cross_sell_page'] = $label;
    }
    return $states;
}, 10, 2);

function pcs_get_page_url(array $args = []): ?string
{
    $page_id = (int) get_option('woocommerce_product_cross_sell_page_id');
    if ($page_id <= 0) return null;
    $url = get_permalink($page_id);
    if (!$url) return null;
    return $args ? add_query_arg($args, $url) : $url;
}

/* ====================================================================================
 * HELPERS: Cross-sell category toggles
 * ================================================================================== */

/**
 * Return TRUE if the top-level parent of $term_id is enabled for cross-sell rows.
 * If the toggle helper/meta is missing, default to TRUE (enabled).
 */
function pcs_is_top_parent_enabled(int $term_id): bool
{
    $taxonomy = 'product_cat';

    // Climb to the top-level ancestor
    $current = get_term($term_id, $taxonomy);
    if (!$current || is_wp_error($current)) {
        return true; // be permissive if term is invalid
    }

    while (!empty($current->parent)) {
        $parent = get_term((int) $current->parent, $taxonomy);
        if (!$parent || is_wp_error($parent)) {
            break;
        }
        $current = $parent;
    }

    $top_id = (int) $current->term_id;

    // Prefer your helper if it exists
    if (function_exists('wc_cs_category_row_enabled')) {
        return (bool) wc_cs_category_row_enabled($top_id);
    }

    // Fallback to raw meta (default enabled)
    $val = get_term_meta($top_id, 'wc_cs_show_row', true);
    return ($val !== 'no');
}

/**
 * Return TRUE if the product is allowed to show (i.e., none of its top-level
 * category ancestors are disabled). Products with no categories are allowed.
 */
function pcs_product_allowed_by_cs_toggle(int $product_id): bool
{
    $terms = get_the_terms($product_id, 'product_cat');
    if (empty($terms) || is_wp_error($terms)) {
        return true; // no categories assigned → allow
    }

    foreach ($terms as $term) {
        if (!pcs_is_top_parent_enabled((int) $term->term_id)) {
            return false; // blocked by a disabled top-level ancestor
        }
    }

    return true;
}

/* ====================================================================================
 * REDIRECT AFTER ADD-TO-CART (DEFERRED)
 * ================================================================================== */

/**
 * Determine cross-sell ids for a product or its parent (variation fallback).
 */
function pcs_get_cross_sell_ids($product_id, $variation_id = 0): array
{
    $target_id = $variation_id ?: $product_id;
    $product   = wc_get_product($target_id);
    if (!$product) return [];

    $ids = $product->get_cross_sell_ids();

    // If variation has none, check parent product
    if (empty($ids) && $product->is_type('variation')) {
        $parent = wc_get_product($product->get_parent_id());
        if ($parent) {
            $ids = $parent->get_cross_sell_ids();
        }
    }
    // Deduplicate
    return array_values(array_unique(array_map('intval', $ids)));
}

/**
 * When an item is added, store redirect info in the WC session (no immediate redirect).
 * For AJAX adds, also set a cookie so we can bounce the user from the current page.
 */
add_action('woocommerce_add_to_cart', function ($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
    // Allow bypass from our cross-sell page to avoid loop
    if (!empty($_POST['wc_pcs_bypass'])) return;

    $ids = pcs_get_cross_sell_ids($product_id, $variation_id);
    if (empty($ids)) return;

    $url = pcs_get_page_url(['product_id' => ($variation_id ?: $product_id)]);
    if (!$url) return;

    if (function_exists('WC') && WC()->session) {
        WC()->session->set('pcs_redirect_url', $url);
        WC()->session->set('pcs_redirect_time', time());
    }

    if (wp_doing_ajax()) {
        wc_setcookie('wc_product_cross_sell_redirect', $url);
    }
}, 10, 6);

/**
 * Perform the actual redirect on the next page load (non-AJAX).
 * This runs after Woo sets cookies so the cart definitely contains the item.
 */
add_action('template_redirect', function () {
    if (is_admin() || !function_exists('WC') || !WC()->session) return;

    $url = WC()->session->get('pcs_redirect_url');
    if (!$url) return;

    // Don't redirect on the cross-sell page itself, or cart/checkout
    $pcs_page_id = (int) get_option('woocommerce_product_cross_sell_page_id');
    if (
        ($pcs_page_id && function_exists('is_page') && is_page($pcs_page_id)) ||
        (function_exists('is_cart') && is_cart()) ||
        (function_exists('is_checkout') && is_checkout())
    ) {
        WC()->session->__unset('pcs_redirect_url');
        WC()->session->__unset('pcs_redirect_time');
        return;
    }

    $safe = esc_url_raw($url);
    WC()->session->__unset('pcs_redirect_url');
    WC()->session->__unset('pcs_redirect_time');

    wp_safe_redirect($safe);
    exit;
});

/**
 * For AJAX adds: a tiny script reads the cookie and redirects.
 */
add_action('wp_footer', function () {
    if (is_admin()) return;
    ?>
    <script>
    (function() {
      function getCookie(name) {
        var m = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
        return m ? decodeURIComponent(m[1]) : null;
      }
      var url = getCookie('wc_product_cross_sell_redirect');
      if (url) {
        document.cookie = 'wc_product_cross_sell_redirect=; Max-Age=0; path=/';
        try { window.location.href = url; } catch (e) {}
      }
    })();
    </script>
    <?php
});

/* ====================================================================================
 * SHORTCODE + TEMPLATE
 * ================================================================================== */

add_shortcode('wc_product_cross_sell_offer', function ($atts) {
    $product_id = absint($_GET['product_id'] ?? 0);
    if (!$product_id) {
        return '<p>' . esc_html__('No product specified.', 'sage') . '</p>';
    }

    $product = wc_get_product($product_id);
    if (!$product) {
        return '<p>' . esc_html__('Product not found.', 'sage') . '</p>';
    }

    // Gather cross-sell IDs from the variation first (if variation), then parent as fallback.
    $xs_ids = [];
    if ($product->is_type('variation')) {
        $xs_ids = (array) $product->get_cross_sell_ids();
        if (!$xs_ids) {
            $parent = wc_get_product($product->get_parent_id());
            if ($parent) {
                $xs_ids = (array) $parent->get_cross_sell_ids();
            }
        }
    } else {
        $xs_ids = (array) $product->get_cross_sell_ids();
    }

    $xs_ids = array_values(array_unique(array_map('intval', $xs_ids)));
    if (empty($xs_ids)) {
        return '<p>' . esc_html__('No cross-sell items.', 'sage') . '</p>';
    }

    // Load products; DO NOT filter by category toggle here.
    $cross_sells = [];
    foreach ($xs_ids as $id) {
        $p = wc_get_product($id);
        if ($p instanceof \WC_Product) {
            $cross_sells[] = $p;
        }
    }

    ob_start();
    wc_get_template(
        'single-product/cross-sell.php',
        [
            'product'     => $product,      // can be a variation or a parent
            'cross_sells' => $cross_sells,  // let the Composer/Blade decide visibility etc.
        ],
        '',
        trailingslashit(get_stylesheet_directory()) . 'woocommerce/'
    );
    return ob_get_clean();
});