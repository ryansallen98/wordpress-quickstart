<?php

/**
 * Theme setup.
 */

namespace App;

use Illuminate\Support\Facades\Vite;
use DirectoryIterator;

/**
 * Define block namespace.
 */
if (!defined('THEME_BLOCK_SLUG')) {
    define('THEME_BLOCK_SLUG', 'wordpress-quickstart-blocks');
}

/**
 * Inject styles into the block editor.
 *
 * @return array
 */
add_filter('block_editor_settings_all', function ($settings) {
    $style = Vite::asset('resources/css/editor.css');

    $settings['styles'][] = [
        'css' => "@import url('{$style}')",
    ];

    return $settings;
});

/**
 * Inject scripts into the block editor.
 *
 * @return void
 */
add_filter('admin_head', function () {
    if (!get_current_screen()?->is_block_editor()) {
        return;
    }

    $dependencies = json_decode(Vite::content('editor.deps.json'));

    foreach ($dependencies as $dependency) {
        if (!wp_script_is($dependency)) {
            wp_enqueue_script($dependency);
        }
    }

    echo Vite::withEntryPoints([
        'resources/ts/editor.ts',
    ])->toHtml();
});

/**
 * Register the theme blocks.
 *
 * @return void
 */
add_action('init', __NAMESPACE__ . '\\theme_blocks_init');

function theme_blocks_init()
{
    // Each subfolder in /resources/views/blocks is a block (must contain block.json)
    $directory = resource_path('views') . '/blocks/';
    if (!is_dir($directory)) {
        return;
    }

    $block_directory = new DirectoryIterator($directory);

    foreach ($block_directory as $block) {
        if ($block->isDir() && !$block->isDot()) {
            // Let WP read block.json in that folder and register it
            register_block_type($block->getRealPath());
        }
    }
}

/**
 * Render callback for ACF blocks using Blade.
 * Expects Blade at: resources/views/blocks/{slug}/{slug}.blade.php
 *
 * @return void
 */
function blade_render_callback($block, string $content = '', bool $is_preview = false, int $post_id = 0)
{
    $slug = str_replace(THEME_BLOCK_SLUG . '/', '', $block['name']);
    $block['slug'] = $slug;

    // Optional: also pass ACF fields to the view
    $fields = function_exists('get_fields') ? (get_fields($post_id) ?: []) : [];

    echo \Roots\view("blocks.{$slug}.template", [
        'block' => $block,
        'fields' => $fields,
        'is_preview' => $is_preview,
        'post_id' => $post_id,
        'content' => $content,
    ])->render();
}

/**
 * Use the generated theme.json file.
 *
 * @return string
 */
add_filter('theme_file_path', function ($path, $file) {
    return $file === 'theme.json'
        ? public_path('build/assets/theme.json')
        : $path;
}, 10, 2);

/**
 * Register the initial theme setup.
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    /**
     * Disable full-site editing support.
     *
     * @link https://wptavern.com/gutenberg-10-5-embeds-pdfs-adds-verse-block-color-options-and-introduces-new-patterns
     */
    remove_theme_support('block-templates');

    /**
     * Register the navigation menus.
     *
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'wordpress-quickstart'),
    ]);

    /**
     * Disable the default block patterns.
     *
     * @link https://developer.wordpress.org/block-editor/developers/themes/theme-support/#disabling-the-default-block-patterns
     */
    remove_theme_support('core-block-patterns');

    /**
     * Enable plugins to manage the document title.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     */
    add_theme_support('title-tag');

    /**
     * Enable post thumbnail support.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    /**
     * Enable responsive embed support.
     *
     * @link https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-support/#responsive-embedded-content
     */
    add_theme_support('responsive-embeds');

    /**
     * Enable HTML5 markup support.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#html5
     */
    add_theme_support('html5', [
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'search-form',
        'script',
        'style',
    ]);

    /**
     * Enable selective refresh for widgets in customizer.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#customize-selective-refresh-widgets
     */
    add_theme_support('customize-selective-refresh-widgets');
}, 20);

/**
 * Register the theme sidebars.
 *
 * @return void
 */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ];

    register_sidebar([
        'name' => __('Primary', 'wordpress-quickstart'),
        'id' => 'sidebar-primary',
    ] + $config);

    register_sidebar([
        'name' => __('Footer', 'wordpress-quickstart'),
        'id' => 'sidebar-footer',
    ] + $config);
});