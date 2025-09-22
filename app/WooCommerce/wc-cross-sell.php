<?php

namespace App\WooCommerce;

/**
 * 1) SETTINGS: add "Cross-sell page" in Advanced → Page setup (right after My account page)
 */
add_filter('woocommerce_get_settings_advanced', function ($settings) {

    // Find the index of the "My account page" field and insert right after it
    $insert_at = null;
    foreach ($settings as $i => $field) {
        if (isset($field['id']) && $field['id'] === 'woocommerce_myaccount_page_id') {
            $insert_at = $i + 1; // insert after My account page
            break;
        }
    }

    $field = [
        'title' => __('Cross-sell page', 'wordpress-quickstart'),
        'desc' => __('Select the page that shows cross-sell offers. Add the shortcode [woocommerce_cross_sell] to that page.', 'wordpress-quickstart'),
        'id' => 'woocommerce_cross_sell_page_id',
        'type' => 'single_select_page',            // same control as Cart/Checkout/My account
        'default' => '',
        'class' => 'wc-enhanced-select-nostd',
        'css' => 'min-width:300px;',
        'desc_tip' => true,
        'autoload' => false,
        'args' => [
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
    } else {
        // Fallback: try to insert before the Page setup section end
        foreach ($settings as $i => $s) {
            if (isset($s['id']) && $s['id'] === 'advanced_page_options_end') {
                array_splice($settings, $i, 0, [$field]);
                return $settings;
            }
        }
        // Ultimate fallback: append (won't be ideal visually, but keeps the option available)
        $settings[] = $field;
    }

    return $settings;
}, 10);

/**
 * 2) Helper: gather viable cross-sell products from the current cart
 */
if (!function_exists('theme_cart_cross_sell_products')) {
    function theme_cart_cross_sell_products(): array
    {
        if (!function_exists('WC') || !WC()->cart || WC()->cart->is_empty())
            return [];

        $ids = [];
        foreach ((array) WC()->cart->get_cart() as $item) {
            if (empty($item['data']) || !is_a($item['data'], \WC_Product::class))
                continue;
            $ids = array_merge($ids, (array) $item['data']->get_cross_sell_ids());
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (!$ids)
            return [];

        $products = wc_get_products([
            'include' => $ids,
            'limit' => -1,
            'status' => 'publish',
            'orderby' => 'include', // keep original order
            'return' => 'objects',
        ]);

        return array_values(array_filter($products, static function ($p) {
            if ($p->is_type('simple')) {
                return $p->is_purchasable() && $p->is_in_stock();
            }
            // Allow variable/grouped/external to show; template already routes to product page
            return 'publish' === $p->get_status();
        }));
    }
}

/**
 * 3) Shortcode: [woocommerce_cross_sell] → loads theme template
 *    Create: wp-content/themes/YOUR_THEME/woocommerce/cross-sell/cross-sell.php
 *    Receives: $products (array<WC_Product>), $checkout_url (string)
 */
add_shortcode('woocommerce_cross_sell', function () {
    if (!function_exists('WC'))
        return '';

    $products = theme_cart_cross_sell_products();

    // Build $rows for the Blade
    $rows = [];
    foreach ($products as $product) {
        /** @var WC_Product $product */
        $rows[] = [
            'id' => $product->get_id(),
            'permalink' => get_permalink($product->get_id()),
            'title_html' => wp_kses_post($product->get_name()),
            'thumb_html' => $product->get_image('woocommerce_thumbnail', ['class' => 'object-cover w-full h-full']),
            'price_html' => wp_kses_post($product->get_price_html()),
            'type' => $product->get_type(),
            'add' => [
                'url' => esc_url($product->add_to_cart_url()),
                'text' => esc_html($product->add_to_cart_text()),
                'classes' => implode(' ', array_filter([
                    'button',
                    'add_to_cart_button',
                    $product->supports('ajax_add_to_cart') ? 'ajax_add_to_cart' : '',
                ])),
                'product_id' => (int) $product->get_id(),
                'sku' => esc_attr($product->get_sku()),
                'quantity' => 1,
                'aria' => esc_attr(sprintf(__('Add “%s” to your cart', 'woocommerce'), wp_strip_all_tags($product->get_name()))),
            ],
        ];
    }

    // Build nonce-protected checkout URL (from earlier logic)
    $token = wp_create_nonce('wc_cross_sell_pass');
    $checkout_url = add_query_arg(
        ['passed_cross_sell' => '1', 'cs_token' => $token],
        wc_get_checkout_url()
    );

    ob_start();
    wc_get_template(
        'cross-sell/cross-sell.php',
        ['rows' => $rows, 'checkout_url' => $checkout_url],
        '',
        trailingslashit(get_stylesheet_directory()) . 'woocommerce/'
    );
    return ob_get_clean();
});

/**
 * 4) Redirect: Checkout → Cross-sell page (only if set)
 *    - Redirect on GET checkout when there ARE cross-sells
 *    - Allow ONLY when arriving with a valid nonce param from the Cross-sell page
 *    - If not set, do nothing
 */
add_action('template_redirect', function () {
    if (is_admin() || wp_doing_ajax())
        return;
    if (!function_exists('WC') || !WC()->cart)
        return;

    $cross_page_id = (int) get_option('woocommerce_cross_sell_page_id');
    if ($cross_page_id <= 0)
        return; // not configured → no redirect

    // Only act on main checkout page GET requests (not pay/received endpoints)
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET')
        return;
    if (!function_exists('is_checkout') || !is_checkout())
        return;
    if (function_exists('is_wc_endpoint_url') && (is_wc_endpoint_url('order-pay') || is_wc_endpoint_url('order-received')))
        return;

    // If no viable cross-sells, don't detour
    if (count(theme_cart_cross_sell_products()) === 0)
        return;

    // If the request includes a valid "passed_cross_sell" nonce, allow checkout
    $passed = isset($_GET['passed_cross_sell']) && $_GET['passed_cross_sell'] === '1';
    $nonce = isset($_GET['cs_token']) ? sanitize_text_field(wp_unslash($_GET['cs_token'])) : '';
    if ($passed && wp_verify_nonce($nonce, 'wc_cross_sell_pass')) {
        return; // legit arrival from Cross-sell page button
    }

    // Otherwise: always detour to the configured Cross-sell page
    $url = get_permalink($cross_page_id);
    if ($url) {
        wp_safe_redirect($url);
        exit;
    }
}, 10);

// 5) Show "— Cross Sell Page" label in the Pages list (like Cart/Checkout/My account)
add_filter('display_post_states', function ($states, $post) {
    if ('page' !== $post->post_type)
        return $states;

    $cross_page_id = (int) get_option('woocommerce_cross_sell_page_id');
    if ($cross_page_id > 0 && $post->ID === $cross_page_id) {
        $label = __('Cross Sell Page', 'wordpress-quickstart');
        $after_keys = ['wc_page_for_checkout', 'wc_page_for_cart', 'wc_page_for_myaccount'];
        $inserted = false;
        $ordered = [];

        foreach ($states as $key => $value) {
            $ordered[$key] = $value;
            if (in_array($key, $after_keys, true) && !$inserted) {
                $ordered['wc_cross_sell_page'] = $label;
                $inserted = true;
            }
        }
        if ($inserted)
            return $ordered;
        $states['wc_cross_sell_page'] = $label;
    }

    return $states;
}, 10, 2);



/**
 * Cross-sell: parent category toggle
 *
 * - Adds a checkbox to the Product Category edit screen (parents only)
 * - Saves to term meta: wc_cs_show_row = 'yes' | 'no'  (default: 'yes')
 * - Helper: wc_cs_category_row_enabled( $term_id ) → bool
 */

// === Helper: is this category row enabled? (default: true) ===
if (!function_exists('wc_cs_category_row_enabled')) {
    function wc_cs_category_row_enabled($term_id): bool {
        $val = get_term_meta((int) $term_id, 'wc_cs_show_row', true);
        // default to enabled unless explicitly "no"
        return $val !== 'no';
    }
}

// === Add field to the Product Category edit form (parents only) ===
add_action('product_cat_edit_form_fields', function ($term) {
    if (!current_user_can('manage_woocommerce') && !current_user_can('manage_product_terms')) {
        return;
    }

    // Only show on *parent* categories
    if (!empty($term->parent)) {
        return;
    }

    $enabled = wc_cs_category_row_enabled($term->term_id);
    $checked = $enabled ? 'checked' : '';
    $nonce   = wp_create_nonce('wc_cs_toggle_cat_row_' . $term->term_id);
    ?>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="wc_cs_show_row"><?php esc_html_e('Show in Cross-sell Page', 'your-textdomain'); ?></label>
        </th>
        <td>
            <label style="display:flex;align-items:center;gap:.5rem;">
                <input type="checkbox" name="wc_cs_show_row" id="wc_cs_show_row" value="1" <?php echo $checked; ?> />
                <span><?php esc_html_e('Render this category row on the Cross-sell page', 'your-textdomain'); ?></span>
            </label>
            <p class="description">
                <?php esc_html_e('This toggle only appears on parent categories. Children are unaffected.', 'your-textdomain'); ?>
            </p>
            <input type="hidden" name="wc_cs_toggle_nonce" value="<?php echo esc_attr($nonce); ?>">
        </td>
    </tr>
    <?php
}, 20);

// === Save the setting ===
add_action('edited_product_cat', function ($term_id) {
    if (!current_user_can('manage_woocommerce') && !current_user_can('manage_product_terms')) {
        return;
    }

    // Only enforce on parents; if a child is edited directly, ignore
    $term = get_term($term_id, 'product_cat');
    if (!$term || is_wp_error($term) || !empty($term->parent)) {
        return;
    }

    $nonce = isset($_POST['wc_cs_toggle_nonce']) ? sanitize_text_field(wp_unslash($_POST['wc_cs_toggle_nonce'])) : '';
    if (!$nonce || !wp_verify_nonce($nonce, 'wc_cs_toggle_cat_row_' . $term_id)) {
        return;
    }

    $enabled = isset($_POST['wc_cs_show_row']) ? 'yes' : 'no';
    update_term_meta($term_id, 'wc_cs_show_row', $enabled);
}, 10);

// === (Optional) Show a small status column in the category list ===
add_filter('manage_edit-product_cat_columns', function ($columns) {
    // Insert a short "Cross-sell" column near the name column
    $new = [];
    foreach ($columns as $key => $label) {
        $new[$key] = $label;
        if ($key === 'name') {
            $new['wc_cs_show_row'] = __('Cross-sell Row', 'your-textdomain');
        }
    }
    return $new;
});

add_filter('manage_product_cat_custom_column', function ($out, $column, $term_id) {
    if ($column !== 'wc_cs_show_row') return $out;

    $term = get_term($term_id, 'product_cat');
    if (!$term || is_wp_error($term)) return $out;

    if (!empty($term->parent)) {
        // Only parents are toggleable; show em dash for children
        return '—';
    }

    return wc_cs_category_row_enabled($term_id)
        ? '<span style="color:#11821b;">' . esc_html__('Enabled', 'your-textdomain') . '</span>'
        : '<span style="color:#a00;">' . esc_html__('Disabled', 'your-textdomain') . '</span>';
}, 10, 3);