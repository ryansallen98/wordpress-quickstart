<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue Vite assets
function theme_enqueue_vite_assets()
{
    $dist_path = get_stylesheet_directory() . '/dist';
    $dist_uri = get_stylesheet_directory_uri() . '/dist';
    $manifest_path = $dist_path . '/.vite/manifest.json';

    if (!file_exists($manifest_path)) {
        return; // nothing built yet
    }

    $manifest = json_decode(file_get_contents($manifest_path), true);
    $entry = 'assets/ts/index.ts';

    if (empty($manifest[$entry])) {
        return;
    }

    $item = $manifest[$entry];

    // Enqueue CSS first (if any)
    if (!empty($item['css'])) {
        foreach ($item['css'] as $i => $cssFile) {
            wp_enqueue_style(
                "theme-{$i}",
                $dist_uri . '/' . $cssFile,
                [],
                null
            );
        }
    }

    // Enqueue main JS
    if (!empty($item['file'])) {
        wp_enqueue_script(
            'theme',
            $dist_uri . '/' . $item['file'],
            [],
            null,
            true
        );
    }
}

// Enqueue Font Awesome
function theme_enqueue_font_awesome()
{
    $fa_rel = 'vendor/components/font-awesome/css/all.min.css';
    $fa_path = get_stylesheet_directory() . '/' . $fa_rel;
    $fa_uri = get_stylesheet_directory_uri() . '/' . $fa_rel;
    $version = null;

    // Try to read Composer's installed.json
    $composer_info = get_stylesheet_directory() . '/vendor/composer/installed.json';
    if (file_exists($composer_info)) {
        $installed = json_decode(file_get_contents($composer_info), true);

        // Format differs between Composer 1/2
        $packages = isset($installed['packages']) ? $installed['packages'] : $installed;
        foreach ($packages as $pkg) {
            if (($pkg['name'] ?? '') === 'components/font-awesome') {
                $version = $pkg['version'] ?? null;
                break;
            }
        }
    }

    if (file_exists($fa_path)) {
        wp_enqueue_style(
            'font-awesome',
            $fa_uri,
            [],
            $version ?: null
        );
    }
}


add_action('wp_enqueue_scripts', 'theme_enqueue_vite_assets');
add_action('wp_enqueue_scripts', 'theme_enqueue_font_awesome');