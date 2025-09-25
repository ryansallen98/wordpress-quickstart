<?php

namespace App\Providers;

use Roots\Acorn\Sage\SageServiceProvider;
use Illuminate\Support\Facades\Vite;

class AcfServiceProvider extends SageServiceProvider
{
    private const THEME_ACF_PATH = '/app/Integrations/acf';

    public function register()
    {
        // Load ACF + ACFE sync paths
        $this->sync_afc_paths();

        // Enqueue custom CSS for ACF Flexible Content admin fields
        $this->enqueue_my_flex_css();
    }


    /*
     * Sync ACF + ACFE paths for JSON and PHP exports
     * This will ensure ACF and ACFE load and save from the theme directory structure
     */
    private function sync_afc_paths()
    {
        add_action('after_setup_theme', function () {
            // ─────────────────────────────────────────────────────────────
            // Field groups - Save JSON + PHP
            // ─────────────────────────────────────────────────────────────
            add_filter('acf/settings/save_json/type=acf-field-group', function () {
                return get_stylesheet_directory() . self::THEME_ACF_PATH . '/json/field-groups';
            }, 10, 1);

            add_filter('acfe/settings/php_save', function () {
                return get_stylesheet_directory() . self::THEME_ACF_PATH . '/php/field-groups';
            }, 10, 1);


            // ─────────────────────────────────────────────────────────────
            // Post types - Save JSON + PHP
            // ─────────────────────────────────────────────────────────────
            add_filter('acfe/settings/json_save/post_types', function () {
                return get_stylesheet_directory() . self::THEME_ACF_PATH . '/json/post-types';
            }, 10, 1);

            add_filter('acfe/settings/php_save/post_types', function () {
                return get_stylesheet_directory() . self::THEME_ACF_PATH . '/php/post-types';
            }, 10, 1);


            // ─────────────────────────────────────────────────────────────
            // Taxonomies - Save JSON + PHP
            // ─────────────────────────────────────────────────────────────
            add_filter('acfe/settings/json_save/taxonomies', function () {
                return get_stylesheet_directory() . self::THEME_ACF_PATH . '/json/taxonomies';
            }, 10, 1);

            add_filter('acfe/settings/php_save/taxonomies', function () {
                return get_stylesheet_directory() . self::THEME_ACF_PATH . '/php/taxonomies';
            }, 10, 1);

            // ─────────────────────────────────────────────────────────────
            // Option pages - Save JSON + PHP
            // ─────────────────────────────────────────────────────────────
            add_filter('acfe/settings/json_save/options_pages', function () {
                return get_stylesheet_directory() . self::THEME_ACF_PATH . '/json/option-pages';
            }, 10, 1);

            add_filter('acfe/settings/php_save/options_pages', function () {
                return get_stylesheet_directory() . self::THEME_ACF_PATH . '/php/options-pages';
            }, 10, 1);


            // ─────────────────────────────────────────────────────────────
            // Forms - Save JSON + PHP
            // ─────────────────────────────────────────────────────────────
            add_filter('acfe/settings/json_save/forms', function () {
                return get_stylesheet_directory() . self::THEME_ACF_PATH . '/json/forms';
            }, 10, 1);

            add_filter('acfe/settings/php_save/forms', function () {
                return get_stylesheet_directory() . self::THEME_ACF_PATH . '/php/forms';
            }, 10, 1);


            // ─────────────────────────────────────────────────────────────
            // Block Types - Save JSON + PHP
            // ─────────────────────────────────────────────────────────────
            add_filter('acfe/settings/json_save/block_types', function () {
                return get_stylesheet_directory() . self::THEME_ACF_PATH . '/json/block-types';
            }, 10, 1);

            add_filter('acfe/settings/php_save/block_types', function () {
                return get_stylesheet_directory() . self::THEME_ACF_PATH . '/php/block-types';
            }, 10, 1);


            // ─────────────────────────────────────────────────────────────
            // Templates - Save JSON + PHP
            // ─────────────────────────────────────────────────────────────
            add_filter('acfe/settings/json_save/templates', function () {
                return get_stylesheet_directory() . self::THEME_ACF_PATH . '/json/templates';
            }, 10, 1);

            add_filter('acfe/settings/php_save/templates', function () {
                return get_stylesheet_directory() . self::THEME_ACF_PATH . '/php/templates';
            }, 10, 1);


            // ─────────────────────────────────────────────────────────────
            // Load JSON + PHP from all type folders (remove default)
            // ─────────────────────────────────────────────────────────────
            add_filter('acf/settings/load_json', function ($paths) {
                unset($paths[0]);
                $paths[] = get_stylesheet_directory() . self::THEME_ACF_PATH . '/json/field-groups';
                $paths[] = get_stylesheet_directory() . self::THEME_ACF_PATH . '/json';
                return $paths;
            }, 10, 1);

            add_filter('acfe/settings/php_load', function ($paths) {
                unset($paths[0]);
                $paths[] = get_stylesheet_directory() . self::THEME_ACF_PATH . '/php/field-groups';
                $paths[] = get_stylesheet_directory() . self::THEME_ACF_PATH . '/php';
                return $paths;
            });
        });
    }


    /*
     * Enqueue CSS for ACF Flexible Content fields
     * This will ensure the styles are loaded in the admin for the flexible content fields
     */
    private function enqueue_my_flex_css()
    {
        add_action('acfe/flexible/enqueue', function ($field, $is_preview) {
            wp_enqueue_style('theme-app', Vite::asset('resources/css/app.css'), [], null);
        }, 10, 2);
    }
}