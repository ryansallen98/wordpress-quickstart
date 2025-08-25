<?php

$context = Timber::context();

$timber_post = Timber::get_post();
$context['post'] = $timber_post;
Timber::render(array('pages/page-' . $timber_post->post_name . '.twig', 'pages/page.twig'), $context);