<?php

/**
 * Theme filters.
 */

namespace App;

/**
 * Add "… Continued" to the excerpt.
 *
 * @return string
 */
add_filter('excerpt_more', function () {
    return sprintf(' &hellip; <a href="%s">%s</a>', get_permalink(), __('Continued', 'wordpress-quickstart'));
});

/**
 * Disable Gutenberg editor for pages using the "Flexible Content" template.
 */
add_filter( 'use_block_editor_for_post', function ( $use_block_editor, $post ) {
    if ( ! $post ) {
        return $use_block_editor;
    }

    if ( $post->post_type === 'page' ) {
        $template = get_page_template_slug( $post );

        if ( $template === 'flexible.blade.php' || $template === 'flexible.php' ) {
            return false; // disable block editor for this template
        }
    }

    return $use_block_editor;
}, 10, 2 );