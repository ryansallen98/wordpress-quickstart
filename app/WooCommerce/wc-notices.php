<?php

namespace App\WooCommerce;

use DOMDocument;
use DOMXPath;


// 1) Helper: parse HTML -> { text, actions[] }
function my_wc_toast_parse_notice_html(string $html): array
{
    // Extract links
    $actions = [];
    if (trim($html) !== '') {
        // Silence parsing warnings for imperfect HTML
        $doc = new DOMDocument();
        @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        $xpath = new DOMXPath($doc);

        foreach ($xpath->query('//a') as $a) {
            /** @var DOMElement $a */
            $href = trim($a->getAttribute('href'));
            $label = trim($a->textContent);
            if ($href !== '') {
                $actions[] = [
                    'label' => $label !== '' ? $label : __('Open', 'your-textdomain'),
                    'href' => $href,
                ];
            }
        }
    }

    // Strip tags for the message text
    $text = trim(
        wp_specialchars_decode(
            wp_strip_all_tags($html),
            ENT_QUOTES
        )
    );

    return [
        'text' => $text,
        'actions' => $actions,
    ];
}

// 2) Helper: map WC notices -> toast objects your Alpine expects
function my_wc_collect_toasts(): array
{
    if (!function_exists('wc_get_notices')) {
        return [];
    }

    $toasts = [];
    $notices = wc_get_notices(); // ['error'=>[], 'success'=>[], 'notice'=>[]]

    $type_map = [
        'error' => 'error',
        'success' => 'success',
        'notice' => 'info',
    ];

    foreach ($notices as $wc_type => $items) {
        $toast_type = $type_map[$wc_type] ?? 'info';

        foreach ((array) $items as $item) {
            // $item can be string or array with 'notice' key
            $html = is_array($item) ? ($item['notice'] ?? '') : (string) $item;
            if ($html === '')
                continue;

            ['text' => $text, 'actions' => $actions] = my_wc_toast_parse_notice_html($html);

            if ($text === '')
                continue;

            $toasts[] = [
                'type' => $toast_type,
                'title' => '',         // keep empty; your template shows it conditionally
                'text' => $text,
                'timeout' => null,       // sticky by default (no auto-dismiss)
                'actions' => array_slice($actions, 0, 3), // up to 3 actions
            ];
        }
    }

    return $toasts;
}

// 3) On normal (non-AJAX) page loads, bootstrap toasts into a global array
add_action('wp_footer', function () {
    if (!function_exists('wc_get_notices'))
        return;

    $toasts = my_wc_collect_toasts();
    if (empty($toasts))
        return;

    // Clear Woo notices since we’re handling them
    if (function_exists('wc_clear_notices')) {
        wc_clear_notices();
    }

    // Push them into your bootstrap array for the Alpine component to flush on init()
    printf(
        '<script>window.__BOOTSTRAP_TOASTS__=(window.__BOOTSTRAP_TOASTS__||[]).concat(%s);</script>',
        wp_json_encode($toasts)
    );
}, 19); // run before </body>, after your markup

// 4) Support AJAX (e.g., add-to-cart via AJAX) using Woo fragments
//    We ship a hidden fragment with JSON, then JS reads & fires toasts.
add_action('wp_footer', function () {
    echo '<div id="wc-toasts-fragment" hidden data-toasts=""></div>';
}, 5);

add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    $toasts = my_wc_collect_toasts();

    if (function_exists('wc_clear_notices')) {
        wc_clear_notices();
    }

    // Put JSON into a data attribute; Woo will replace #wc-toasts-fragment
    $html = sprintf(
        '<div id="wc-toasts-fragment" hidden data-toasts="%s"></div>',
        esc_attr(wp_json_encode($toasts))
    );

    // Key must match the selector of the element we’re replacing
    $fragments['#wc-toasts-fragment'] = $html;
    return $fragments;
}, 10, 1);



/**
 * Allow inline SVG (and classes) inside WooCommerce notices only.
 */
add_filter('woocommerce_kses_notice_allowed_tags', function ($tags) {

    // Allow the <svg> element + common attributes.
    $tags['svg'] = array(
        'class' => true,
        'xmlns' => true,
        'width' => true,
        'height' => true,
        'viewBox' => true,
        'aria-hidden' => true,
        'role' => true,
        'focusable' => true,
        'fill' => true,
        'stroke' => true,
        'stroke-width' => true,
        'viewbox' => true,
    );

    // Allow basic shape/path elements + styling hooks.
    $tags['path'] = array(
        'class' => true,
        'd' => true,
        'fill' => true,
        'stroke' => true,
        'stroke-width' => true,
        'fill-rule' => true,
        'clip-rule' => true,
    );
    $tags['g'] = array('class' => true);
    $tags['title'] = array(); // optional, for accessibility

    return $tags;
});
