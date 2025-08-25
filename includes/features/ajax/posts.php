<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    return;
}

// AJAX posts
add_action('wp_ajax_nopriv_htmx_posts', 'htmx_posts');
add_action('wp_ajax_htmx_posts', 'htmx_posts');

function htmx_posts()
{
    // Security
    check_ajax_referer('htmx_wp', 'nonce');

    // Params
    $s = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
    $paged = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;
    $ppp = (int) get_option('posts_per_page'); // or a fixed number

    // Query
    $args = [
        'post_type' => 'post',
        'post_status' => ['publish'],
        's' => $s,
        'posts_per_page' => $ppp,
        'paged' => $paged,
        'no_found_rows' => false, // keep this false so found_posts/max_num_pages are populated
    ];

    $query = new WP_Query($args);
    $posts = Timber::get_posts($query);

    // Totals/range (respecting the search filter)
    $total = (int) $query->found_posts;         // total results for this query
    $count = count($posts);                     // posts on this page
    $start = $total ? (($paged - 1) * $ppp) + 1 : 0;
    $end = $total ? min($start + $count - 1, $total) : 0;

    // Context
    $context = [
        'posts' => $posts,
        'search' => $s,
        'posts_per_page' => $ppp,
        'paged' => $paged,
        'total_posts' => $total,
        'max_pages' => (int) $query->max_num_pages,
        'range' => ['start' => $start, 'end' => $end],
        'has_more' => ($query->max_num_pages > $paged),
        'next_page' => $paged + 1,
    ];

    Timber::render('partials/post-loop.twig', $context);
    wp_die();
}