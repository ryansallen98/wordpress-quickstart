<?php
/**
 * Home/Archive controller (Timber)
 * - Uses the MAIN WP query (respects search/tax filters)
 * - Adds taxonomy data for the resolved post type
 * - Exposes pagination totals, range, and HTMX-friendly page links
 */

$context = Timber::context();
$posts_page_id = (int) get_option('page_for_posts');
$context['posts_url'] = $posts_page_id ? get_permalink($posts_page_id) : home_url('/');
$context['posts_url'] = trailingslashit($context['posts_url']);

/**
 * 1) Posts from the MAIN query
 * Timber::get_posts() wraps $wp_query — no extra DB hit, respects filters
 */
$context['posts'] = Timber::get_posts();

/**
 * 2) Resolve the post type for this view
 * Try the query var, then the first post’s type, otherwise 'post'
 */
$post_type = get_query_var('post_type') ?: ($context['posts'][0]->post_type ?? 'post');

/**
 * 3) Taxonomies (public only) for the resolved post type
 */
$tax_objects = array_filter(
    get_object_taxonomies($post_type, 'objects'),
    static fn($tax) => !empty($tax->public)
);

$context['taxonomies'] = array_map(
    static fn($tax) => [
        'name' => $tax->name,
        'label' => $tax->label,
        'hierarchical' => (bool) $tax->hierarchical,
        'public' => (bool) $tax->public,
        'terms' => get_terms([
            'taxonomy' => $tax->name,
            'hide_empty' => false, // set true if you only want terms that have posts
        ]),
    ],
    $tax_objects
);

/**
 * 4) Pagination & totals from the MAIN query
 * Use $wp_query->found_posts / max_num_pages so totals match current filters.
 */
global $wp_query;

$ppp = (int) ($wp_query->get('posts_per_page') ?: get_option('posts_per_page'));
$current = max(1, (int) get_query_var('paged'));
$total_posts = isset($wp_query->found_posts) ? (int) $wp_query->found_posts : 0;
$max_pages = isset($wp_query->max_num_pages) ? (int) $wp_query->max_num_pages : 0;

$context['posts_per_page'] = $ppp;
$context['paged'] = $current;
$context['total_posts'] = $total_posts;
$context['max_pages'] = $max_pages;

/**
 * 5) Human-readable range (e.g., "Showing 11–20 of 134")
 */
$shown = count($context['posts']);
$start = $total_posts ? (($current - 1) * $ppp + 1) : 0;
$end = $total_posts ? min($start + $shown - 1, $total_posts) : 0;
$context['range'] = ['start' => $start, 'end' => $end];

/**
 * 6) Capture current query params you care about (e.g., search)
 */
$params = [
    's' => isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '',
];
$context['params'] = $params;

/**
 * 7) Render
 */
Timber::render('pages/home.twig', $context);