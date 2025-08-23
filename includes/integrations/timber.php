<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Initialize Timber.
Timber\Timber::init();

/**
 * Expose tailwind-merge to Twig as:
 *  - filter:   {{ 'px-2 px-4'|twmerge }}
 *  - function: {{ twmerge(['px-2', 'px-4']) }}
 *
 * Requires: composer require gehrisandro/tailwind-merge-php
 */
add_filter('timber/twig', function (\Twig\Environment $twig) {
    // Try to resolve a TailwindMerge instance from common namespaces.
    $merge = null;

    if (class_exists('\TailwindMerge\TailwindMerge')) {
        $merge = \TailwindMerge\TailwindMerge::instance();
    } elseif (class_exists('\TitasGailius\TailwindMerge\TailwindMerge')) {
        // Example of another possible namespace – adjust if your package differs.
        $merge = \TitasGailius\TailwindMerge\TailwindMerge::instance();
    } elseif (class_exists('\TailwindMerge')) {
        // If the class really is global.
        $merge = \TailwindMerge::instance();
    }

    // Callable used by both filter and function.
    $callable = $merge
        ? static fn($classes) => $merge->merge($classes)
        : static fn($classes) => $classes; // no-op so Twig never errors

    $twig->addFilter(new \Twig\TwigFilter('twmerge', $callable));
    $twig->addFunction(new \Twig\TwigFunction('twmerge', $callable));

    return $twig;
});