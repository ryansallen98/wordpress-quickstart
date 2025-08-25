<?php
$context = Timber::context();

// Posts from the main query
$context['posts'] = Timber::get_posts();

// Resolve post type
$post_type = get_query_var('post_type') ?: ($context['posts'][0]->post_type ?? 'post');

// Taxonomies (public only)
$tax_objects = array_filter(
    get_object_taxonomies($post_type, 'objects'),
    fn($tax) => $tax->public
);

$context['taxonomies'] = array_map(
    fn($tax) => [
        'name' => $tax->name,
        'label' => $tax->label,
        'hierarchical' => (bool) $tax->hierarchical,
        'public' => (bool) $tax->public,
        'terms' => get_terms([
            'taxonomy' => $tax->name,
            'hide_empty' => false,
        ]),
    ],
    $tax_objects
);

// Pagination & counts — use the MAIN QUERY's totals so filters/search are respected
global $wp_query;

$context['posts_per_page'] = (int) get_query_var('posts_per_page') ?: (int) get_option('posts_per_page');
$context['paged'] = max(1, (int) get_query_var('paged'));
$context['total_posts'] = isset($wp_query->found_posts) ? (int) $wp_query->found_posts : 0;
$context['max_pages'] = isset($wp_query->max_num_pages) ? (int) $wp_query->max_num_pages : 0;

$shown = count($context['posts']);
$start = $context['total_posts'] ? (($context['paged'] - 1) * $context['posts_per_page'] + 1) : 0;
$end = $context['total_posts'] ? min($start + $shown - 1, $context['total_posts']) : 0;

$context['range'] = ['start' => $start, 'end' => $end];

Timber::render('pages/home.twig', $context);