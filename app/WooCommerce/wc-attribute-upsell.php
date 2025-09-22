<?php
/**
 * Attribute Upsell (Theme-side, Sage)
 *
 * - Admin UI to toggle upsell per attribute
 * - Woo settings: "Attribute upgrade page"
 * - Intercept add-to-cart -> redirect to upgrade page when not-top term selected
 * - Shortcode renders theme template: your-theme/woocommerce/single-product/attribute-upgrade.php
 * - Forwards ALL user inputs (incl. APF text fields) through WC session token
 */

namespace App\WooCommerce;

if (!defined('ABSPATH')) { exit; }

/* ====================================================================================
 * SETTINGS: "Attribute upgrade page"
 * ================================================================================== */

add_filter('woocommerce_get_settings_advanced', function (array $settings) {
    $insert_at = null;
    foreach ($settings as $i => $field) {
        if (!empty($field['id']) && $field['id'] === 'woocommerce_myaccount_page_id') {
            $insert_at = $i + 1; break;
        }
    }

    $field = [
        'title'    => __('Attribute upgrade page', 'sage'),
        'desc'     => __('Select the page that shows the attribute upgrade offers. Add the shortcode [wc_attribute_upgrade_offer] to that page.', 'sage'),
        'id'       => 'woocommerce_attribute_upgrade_page_id',
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

    $page_id = (int) get_option('woocommerce_attribute_upgrade_page_id');
    if ($page_id > 0 && (int) $post->ID === $page_id) {
        $label = __('Attribute Upgrade Page', 'sage');
        $after_keys = ['wc_page_for_checkout', 'wc_page_for_cart', 'wc_page_for_myaccount'];
        $inserted = false; $ordered = [];
        foreach ($states as $key => $value) {
            $ordered[$key] = $value;
            if (in_array($key, $after_keys, true) && !$inserted) {
                $ordered['wc_attribute_upgrade_page'] = $label;
                $inserted = true;
            }
        }
        if ($inserted) return $ordered;
        $states['wc_attribute_upgrade_page'] = $label;
    }
    return $states;
}, 10, 2);

function get_attribute_upgrade_page_url(array $args = []): ?string
{
    $page_id = (int) get_option('woocommerce_attribute_upgrade_page_id');
    if ($page_id <= 0) return null;
    $url = get_permalink($page_id);
    if (!$url) return null;
    return $args ? add_query_arg($args, $url) : $url;
}

/* ====================================================================================
 * ADMIN: Attribute toggle under "Configure terms"
 * ================================================================================== */

add_action('admin_init', __NAMESPACE__ . '\\maybe_handle_save');
add_action('in_admin_footer', __NAMESPACE__ . '\\render_toggle_under_terms_table');

function wc_attribute_upsell_enabled(string $taxonomy): bool
{
    $opt = get_option('wc_attr_upsell_' . $taxonomy, 'no');
    return $opt === 'yes';
}

function maybe_handle_save(): void
{
    if (!is_admin()) return;

    if (
        isset($_POST['wc_attr_upsell_toggle_submit'], $_POST['taxonomy'], $_POST['wc_attr_upsell_nonce']) &&
        wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wc_attr_upsell_nonce'])), 'wc_attr_upsell_save')
    ) {
        if (!current_user_can('manage_woocommerce') && !current_user_can('manage_options')) {
            wp_die(esc_html__('Sorry, you are not allowed to do that.', 'sage'));
        }

        $taxonomy = sanitize_text_field(wp_unslash($_POST['taxonomy'] ?? ''));
        if (strpos($taxonomy, 'pa_') !== 0) {
            wp_die(esc_html__('Invalid taxonomy.', 'sage'));
        }

        update_option('wc_attr_upsell_' . $taxonomy, isset($_POST['wc_attr_upsell_toggle']) ? 'yes' : 'no');

        $redirect = add_query_arg(
            ['taxonomy' => $taxonomy, 'post_type' => 'product', 'wc_attr_us' => get_option('wc_attr_upsell_' . $taxonomy, 'no')],
            admin_url('edit-tags.php')
        );
        wp_safe_redirect($redirect);
        exit;
    }
}

function render_toggle_under_terms_table(): void
{
    if (!function_exists('get_current_screen')) return;
    $screen = get_current_screen();
    if (!$screen || $screen->base !== 'edit-tags') return;

    $taxonomy  = isset($_GET['taxonomy']) ? sanitize_text_field(wp_unslash($_GET['taxonomy'])) : '';
    $post_type = isset($_GET['post_type']) ? sanitize_text_field(wp_unslash($_GET['post_type'])) : '';
    if ($post_type !== 'product' || strpos($taxonomy, 'pa_') !== 0) return;

    $checked = wc_attribute_upsell_enabled($taxonomy) ? 'checked' : '';
    $action  = esc_url(admin_url('edit-tags.php?taxonomy=' . $taxonomy . '&post_type=product'));
    $nonce   = wp_create_nonce('wc_attr_upsell_save');

    $tax_obj = get_taxonomy($taxonomy);
    $label   = $tax_obj && isset($tax_obj->labels->singular_name) ? $tax_obj->labels->singular_name : $taxonomy;
    ?>
    <div class="wc-attr-upsell-toggle" style="margin:16px 0 24px; padding:16px; background:#fff; border:1px solid #ccd0d4; border-radius:4px;">
        <form method="post" action="<?php echo $action; ?>">
            <input type="hidden" name="taxonomy" value="<?php echo esc_attr($taxonomy); ?>">
            <input type="hidden" name="wc_attr_upsell_nonce" value="<?php echo esc_attr($nonce); ?>">
            <h2 style="margin:0 0 12px;"><?php echo esc_html(sprintf(__('Upselling for %s', 'sage'), $label)); ?></h2>
            <label style="display:flex; align-items:center; gap:8px; font-weight:600; margin-bottom:12px;">
                <input type="checkbox" name="wc_attr_upsell_toggle" <?php echo $checked; ?> />
                <?php echo esc_html(sprintf(__('Enable upselling for %s', 'sage'), $label)); ?>
            </label>
            <button type="submit" class="button button-primary" name="wc_attr_upsell_toggle_submit" value="1">
                <?php esc_html_e('Save', 'sage'); ?>
            </button>
            <?php
            if (isset($_GET['wc_attr_us'])) {
                $msg = $_GET['wc_attr_us'] === 'yes' ? __('Upselling enabled.', 'sage') : __('Upselling disabled.', 'sage');
                echo '<span style="margin-left:10px;">' . esc_html($msg) . '</span>';
            }
            ?>
        </form>
    </div>
    <script>
    (function() {
        var panel = document.querySelector('.wc-attr-upsell-toggle');
        if (!panel) return;
        var table = document.querySelector('.wrap .wp-list-table');
        if (table && table.parentNode) {
            table.parentNode.insertBefore(panel, table.nextSibling);
        }
    })();
    </script>
    <?php
}

/* ====================================================================================
 * FRONTEND: Intercept add-to-cart and shortcode -> THEME TEMPLATE
 * ================================================================================== */

use WC_Product_Variable;

// IMPORTANT: allow up to 6 args (WC varies between 3/5/6)
add_filter('woocommerce_add_to_cart_validation', __NAMESPACE__ . '\\intercept_attribute_upsell', 10, 6);
add_shortcode('wc_attribute_upgrade_offer', __NAMESPACE__ . '\\render_upgrade_offer_shortcode');

/**
 * Collect ALL non-internal POST fields (scalars & arrays) to forward via WC session.
 * Includes attribute_* too so plugins relying on original POST see them again.
 */
function wcau_collect_forward_extras(array $post): array
{
    // Do not forward these exact keys
    $deny_keys = array_map('strtolower', [
        'add-to-cart','product_id','variation_id','quantity',
        'wc_attr_upgrade_bypass','wc_attr_upgrade_nonce',
        'security','nonce','action','wc-ajax',
        'woocommerce-login-nonce','woocommerce-reset-password-nonce',
    ]);

    // ⬇️ Add 'attribute_' here so attributes are NOT forwarded via session
    $deny_prefixes = array_map('strtolower', [
        'attribute_',        // <— avoid conflicts; rebuilt on the upgrade page
        '_wp','_wc','woocommerce_','g-recaptcha',
    ]);

    $sanitize = function ($value) use (&$sanitize) {
        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) { $out[$k] = $sanitize($v); }
            return $out;
        }
        return is_scalar($value) ? wc_clean(wp_unslash((string) $value)) : '';
    };

    $extras = [];
    foreach ($post as $key => $value) {
        $lk = strtolower($key);
        if (in_array($lk, $deny_keys, true)) continue;

        $deny = false;
        foreach ($deny_prefixes as $pref) {
            if ($pref !== '' && strpos($lk, $pref) === 0) { $deny = true; break; }
        }
        if ($deny) continue;

        $extras[$key] = $sanitize($value);
    }
    return $extras;
}

/**
 * Intercept add-to-cart for VARIABLE products only.
 * Signature supports 3/5/6 params (WC versions & handlers differ).
 */
function intercept_attribute_upsell(
    $passed,
    $product_id,
    $quantity,
    $variation_id = 0,
    $variations = [],
    $cart_item_data = []
) {
    // BYPASS when submitting from upgrade page (prevents loops)
    if (
        isset($_POST['wc_attr_upgrade_bypass'], $_POST['wc_attr_upgrade_nonce']) &&
        wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wc_attr_upgrade_nonce'])), 'wc_attr_upgrade_add')
    ) {
        return $passed;
    }

    // If no upgrade page configured, do nothing
    $upgrade_url_base = get_attribute_upgrade_page_url();
    if (!$upgrade_url_base) return $passed;

    // Avoid loop if already on the configured upgrade page
    if (function_exists('is_page') && is_page((int) get_option('woocommerce_attribute_upgrade_page_id'))) {
        return $passed;
    }

    // Only intercept VARIABLE products; simple/grouped/external should proceed normally
    $product = wc_get_product($product_id);
    if (!($product instanceof WC_Product_Variable)) {
        return $passed; // ← fixes your fatal on simple products
    }

    // Collect chosen attributes (from POST + $variations)
    $chosen = array_change_key_case((array) $variations, CASE_LOWER);
    foreach ($_POST as $k => $v) {
        if (strpos($k, 'attribute_') === 0) {
            $chosen[strtolower($k)] = wc_clean(wp_unslash($v));
        }
    }

    // Find toggled-ON attributes on this product
    $flagged_taxonomies = [];
    foreach ($product->get_attributes() as $attr) {
        if ($attr instanceof \WC_Product_Attribute) {
            $taxonomy = $attr->get_name(); // e.g. pa_size
            if ($attr->get_variation() && wc_attribute_upsell_enabled($taxonomy)) {
                $flagged_taxonomies[] = $taxonomy;
            }
        }
    }
    if (!$flagged_taxonomies) return $passed;

    foreach ($flagged_taxonomies as $taxonomy) {
        $attr_key      = 'attribute_' . strtolower($taxonomy);
        $selected_term = $chosen[$attr_key] ?? '';
        if ($selected_term === '') continue;

        $top_term = get_top_priced_term_for_attribute($product, $taxonomy);
        if (!$top_term) continue;

        if (sanitize_title($selected_term) !== sanitize_title($top_term)) {
            // Build args + stash ALL POST (incl. APF) into WC session with a token
            $args = [
                'product_id' => $product_id,
                'taxonomy'   => $taxonomy,
                'selected'   => $selected_term,
            ] + filter_forward_attrs($chosen);

            $extras = wcau_collect_forward_extras($_POST);
            if (!empty($extras) && function_exists('WC') && WC()->session) {
                $token = wp_generate_uuid4();
                WC()->session->set('wcau_' . $token, $extras);
                $args['wcau'] = $token;
            }

            $upgrade_url = get_attribute_upgrade_page_url($args);

            if ($upgrade_url) {
                wc_add_notice(__('You’re almost there — pick your perfect option first.', 'sage'), 'notice');

                if (wp_doing_ajax()) {
                    wp_send_json([
                        'error'       => true,
                        'product_url' => esc_url_raw($upgrade_url),
                    ]);
                } else {
                    wp_safe_redirect($upgrade_url);
                    exit;
                }
                return false;
            }
        }
    }
    return $passed;
}

function get_top_priced_term_for_attribute(WC_Product_Variable $product, string $taxonomy): ?string
{
    $taxonomy = strtolower($taxonomy);
    $attr_key = 'attribute_' . $taxonomy;

    $best_per_term = [];
    foreach ($product->get_available_variations() as $var) {
        if ((isset($var['is_purchasable']) && !$var['is_purchasable']) ||
            (isset($var['is_in_stock']) && !$var['is_in_stock'])) {
            continue;
        }
        if (empty($var['attributes'][$attr_key])) continue;

        $term_slug = sanitize_title($var['attributes'][$attr_key]);
        $price = isset($var['display_price'])
            ? (float) $var['display_price']
            : (float) ($var['display_regular_price'] ?? 0);

        if ($price <= 0 && !apply_filters('wc_attr_upgrade_allow_zero_price', false, $product->get_id())) {
            continue;
        }

        if (!isset($best_per_term[$term_slug]) || $price > $best_per_term[$term_slug]) {
            $best_per_term[$term_slug] = $price;
        }
    }
    if (!$best_per_term) return null;
    arsort($best_per_term, SORT_NUMERIC);
    $top_term = array_key_first($best_per_term);
    return $top_term ?: null;
}

function get_best_variation_for_term(WC_Product_Variable $product, string $taxonomy, string $term_slug, array $locks = []): ?array
{
    $taxonomy  = strtolower($taxonomy);
    $attr_key  = 'attribute_' . $taxonomy;
    $term_slug = sanitize_title($term_slug);
    $locks     = array_change_key_case($locks, CASE_LOWER);

    $best = null; $best_price = -1;

    foreach ($product->get_available_variations() as $var) {
        if (empty($var['attributes'][$attr_key]) || sanitize_title($var['attributes'][$attr_key]) !== $term_slug) continue;

        // Must match any locked attributes coming via URL
        $matches_locks = true;
        foreach ($locks as $k => $v) {
            if (strpos($k, 'attribute_') !== 0) continue;
            if (!isset($var['attributes'][$k])) continue;
            if ($v !== '' && sanitize_title($var['attributes'][$k]) !== sanitize_title($v)) {
                $matches_locks = false; break;
            }
        }
        if (!$matches_locks) continue;

        if ((isset($var['is_purchasable']) && !$var['is_purchasable']) ||
            (isset($var['is_in_stock']) && !$var['is_in_stock'])) {
            continue;
        }

        $price = isset($var['display_price'])
            ? (float) $var['display_price']
            : (float) ($var['display_regular_price'] ?? 0);

        if ($price <= 0 && !apply_filters('wc_attr_upgrade_allow_zero_price', false, $product->get_id())) {
            continue;
        }

        if ($price > $best_price) {
            $best_price = $price;
            $best = $var;
        }
    }

    return $best;
}

function filter_forward_attrs(array $attrs): array
{
    $out = [];
    foreach ($attrs as $k => $v) {
        if (strpos(strtolower($k), 'attribute_') === 0) {
            $out[$k] = $v;
        }
    }
    return $out;
}

/**
 * Shortcode → load theme template:
 *   your-theme/woocommerce/single-product/attribute-upgrade.php
 * Passes $extras restored from session (wcau token).
 */
function render_upgrade_offer_shortcode(): string
{
    $product_id = absint($_GET['product_id'] ?? 0);
    $taxonomy   = sanitize_text_field($_GET['taxonomy'] ?? '');
    $selected   = sanitize_text_field($_GET['selected'] ?? '');
    $token      = sanitize_text_field($_GET['wcau'] ?? '');

    if (!$product_id || !$taxonomy) {
        return '<p>' . esc_html__('Nothing to upgrade here.', 'sage') . '</p>';
    }

    $product = wc_get_product($product_id);
    if (!$product instanceof WC_Product_Variable) {
        return '<p>' . esc_html__('Product not found or not variable.', 'sage') . '</p>';
    }

    $tax_obj = get_taxonomy($taxonomy);
    $label   = $tax_obj && isset($tax_obj->labels->singular_name) ? $tax_obj->labels->singular_name : $taxonomy;

    // Locks = chosen attributes other than the upsell taxonomy
    $locks = [];
    foreach ($_GET as $k => $v) {
        if (strpos(strtolower($k), 'attribute_') === 0 && strtolower($k) !== 'attribute_' . strtolower($taxonomy)) {
            $locks[$k] = sanitize_text_field($v);
        }
    }

    // Restore extra fields from session
    $extras = [];
    if ($token && function_exists('WC') && WC()->session) {
        $extras = WC()->session->get('wcau_' . $token, []);
        // Optionally clear immediately:
        // WC()->session->__unset('wcau_' . $token);
    }

    // Build rows
    $terms = wc_get_product_terms($product_id, $taxonomy, ['fields' => 'all']);
    if (is_wp_error($terms) || empty($terms)) {
        return '<p>' . esc_html__('No options available.', 'sage') . '</p>';
    }

    $rows = [];
    foreach ($terms as $term) {
        $best = get_best_variation_for_term($product, $taxonomy, $term->slug, $locks);
        if (!$best) continue;

        $rows[] = [
            'term_name'    => $term->name,
            'term_slug'    => $term->slug,
            'variation_id' => (int) $best['variation_id'],
            'price'        => (float) ($best['display_price'] ?? $best['display_regular_price'] ?? 0),
            'attributes'   => $best['attributes'],
        ];
    }

    if (!$rows) {
        return '<p>' . esc_html__('No upgrade options available.', 'sage') . '</p>';
    }

    usort($rows, function($a, $b) { return $b['price'] <=> $a['price']; });

    ob_start();
    wc_get_template(
        'single-product/attribute-upgrade.php',
        [
            'product'  => $product,
            'label'    => $label,
            'selected' => $selected,
            'rows'     => $rows,
            'extras'   => $extras, // <<< pass to template
        ],
        '',
        trailingslashit(get_stylesheet_directory()) . 'woocommerce/'
    );
    return ob_get_clean();
}