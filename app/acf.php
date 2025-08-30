<?php

/**
 * Theme ACF customizations
 *
 * Purpose
 * -------
 * Centralize JSON/PHP locations and loading for:
 * - ACF 6.2+ UI objects: Field Groups, Post Types, Taxonomies, UI Options Pages
 * - ACFE Dynamic objects: Block Types, Forms, etc.
 *
 * What syncs automatically
 * ------------------------
 * - ACF Local JSON: Field Groups, Post Types, Taxonomies, UI Options Pages
 *   • These are saved/loaded from per-type folders configured below.
 *   • Deploy the JSON files to keep environments in sync.
 *   • Click “Sync” in WP Admin only if you want to edit them on that environment.
 *
 * ACFE Pro vs Free
 * ----------------
 * - ACFE Pro can auto-sync JSON/PHP for Dynamic objects (Block Types, Forms, etc.).
 * - WITHOUT ACFE PRO:
 *   • Block Types and Forms DO NOT auto-sync.
 *   • You MUST export them manually (ACF → Tools → Export → PHP) and place
 *     the files into /resources/acf/block-types/php and /resources/acf/forms/php.
 *   • If you export to PHP, they’ll be loaded by the manual loader below
 *     (code-driven, not editable in the Block Types/Forms UI).
 *
 * Priority rules
 * --------------
 * - If both JSON and PHP exist for the same object, PHP (code) wins and the UI entry
 *   will be read-only or hidden.
 */

namespace App;

/**
 * ACF field groups JSON + PHP auto sync handling
 *
 * If you have ACFE Pro installed you can auto sync option pages, post types, field groups,
 * and more. In that case you can delete the manual PHP imports further down and just modify
 * the JSON + PHP sync paths here.
 *
 * Example: Post types sync docs
 * https://www.acf-extended.com/features/modules/post-types/json-php-sync
 */

// Change where ACF saves JSON to

// Field groups
add_filter('acf/settings/save_json/type=acf-field-group', function ($path) {
  return get_stylesheet_directory() . '/resources/acf/field-groups/json';
}, 10, 1);

// Post types
add_filter('acf/settings/save_json/type=acf-post-type', function ($path) {
  return get_stylesheet_directory() . '/resources/acf/post-types/json';
}, 10, 1);

// Taxonomies
add_filter('acf/settings/save_json/type=acf-taxonomy', function ($path) {
  return get_stylesheet_directory() . '/resources/acf/taxonomies/json';
}, 10, 1);

// UI Options Pages
add_filter('acf/settings/save_json/type=acf-ui-options-page', function ($path) {
  return get_stylesheet_directory() . '/resources/acf/option-pages/json';
}, 10, 1);

// Change where ACF loads JSON from
add_filter('acf/settings/load_json', function ($paths) {
  unset($paths[0]);

  $paths[] = get_stylesheet_directory() . '/resources/acf/field-groups/json';
  $paths[] = get_stylesheet_directory() . '/resources/acf/post-types/json';
  $paths[] = get_stylesheet_directory() . '/resources/acf/taxonomies/json';
  $paths[] = get_stylesheet_directory() . '/resources/acf/option-pages/json';

  return $paths;
}, 10, 1);

// Change where ACFE saves PHP exports
add_filter('acfe/settings/php_save', function () {
    return get_stylesheet_directory() . '/resources/acf/field-groups/php';
}, 10, 1);

// Change where ACFE loads PHP exports from
add_filter('acfe/settings/php_load', function ($paths) {
    $paths[] = get_stylesheet_directory() . '/resources/acf/field-groups/php';
    return $paths;
});


/**
 * Load ACF/ACFE PHP exports manually
 *
 * If you have ACFE Pro installed you can comment out or delete these manual PHP imports.
 * This function scans your custom resources/acf/.../php directories and includes all files.
 */
add_action('acf/init', function () {
    $base = get_stylesheet_directory() . '/resources/acf';

    $folders = [
        'block-types/php',
        'option-pages/php',
        'post-types/php',
        'taxonomies/php',
        'forms/php',
    ];

    foreach ($folders as $folder) {
        $dir = $base . '/' . $folder;

        if (!is_dir($dir)) {
            continue;
        }

        foreach (glob($dir . '/*.php') as $file) {
            include_once $file;
        }
    }
});