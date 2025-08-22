<?php
use Timber\Timber;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue Prism assets
function theme_enqueue_prism_assets()
{
    if (is_admin())
        return;
    if (!is_user_logged_in() || !current_user_can('manage_options'))
        return;

    $dist_path = get_stylesheet_directory() . '/dist';
    $dist_uri = get_stylesheet_directory_uri() . '/dist';
    $manifest_path = $dist_path . '/.vite/manifest.json';

    if (!file_exists($manifest_path)) {
        return; // nothing built yet
    }

    $manifest = json_decode(file_get_contents($manifest_path), true);
    $entry = 'assets/ts/debug.ts';

    if (empty($manifest[$entry])) {
        return;
    }

    $item = $manifest[$entry];

    // Enqueue CSS first (if any)
    if (!empty($item['css'])) {
        foreach ($item['css'] as $i => $cssFile) {
            wp_enqueue_style(
                "debug-css-{$i}",
                $dist_uri . '/' . $cssFile,
                [],
                null
            );
        }
    }

    // Enqueue main JS
    if (!empty($item['file'])) {
        wp_enqueue_script(
            'debug-js',
            $dist_uri . '/' . $item['file'],
            [],
            null,
            true
        );
    }
}

add_action('wp_enqueue_scripts', 'theme_enqueue_prism_assets');

// Frontend-only admin debug widget via Timber
add_action('wp_footer', function () {
    if (is_admin())
        return;
    if (!is_user_logged_in() || !current_user_can('manage_options'))
        return;

    // render debug widget
    $context = Timber::context();
    Timber::render(__DIR__ . '/debug.twig', $context);
}, 100);