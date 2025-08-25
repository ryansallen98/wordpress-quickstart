<?php

$context = Timber::context();
$templates = array('pages/index.twig');
if (is_home()) {
    array_unshift($templates, 'pages/front-page.twig', 'pages/home.twig', 'pages/page.twig');
}
Timber::render($templates, $context);