<?php


add_action('after_setup_theme', function () {
    add_theme_support("title-tag");
    add_theme_support("post-thumbnails");
    add_theme_support("html5", ["search-form", "gallery", "caption"]);
    add_theme_support('menus');

    remove_theme_support('block-templates');
});

// Remove the Site Editor from the admin menu.
add_action('admin_menu', function () {
    remove_submenu_page('themes.php', 'site-editor.php');
}, 999);


// // Disable Gutenberg for all pages
// add_filter('use_block_editor_for_post_type', function ($use_block_editor, $post_type) {
//     if ($post_type === 'page') {
//         return false;
//     }
//     return $use_block_editor;
// }, 10, 2);

// // Force Classic editor globally (all post types)
// add_filter('use_block_editor_for_post_type', function ($use_block_editor, $post_type) {
//     return false; // disable Gutenberg everywhere
// }, 10, 2);