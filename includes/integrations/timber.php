<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Initialize Timber.
Timber\Timber::init();

/**
 * Expose to Twig:
 *  - tailwind-merge
 *      filter:   {{ 'px-2 px-4'|twmerge }}
 *      function: {{ twmerge(['px-2', 'px-4']) }}
 *      requires: composer require gehrisandro/tailwind-merge-php
 *
 *  - lucide (SVG icons)
 *      function: {{ lucide('activity') }}
 *                 {{ lucide('heart', {'class':'w-5 h-5 text-red-500'}) }}
 *                 {{ lucide('bell', {'stroke-width':1.5}, 'Notifications') }}
 *      requires: composer require natewiebe13/php-lucide
 */
add_filter('timber/twig', function (\Twig\Environment $twig) {
    /** ----------------------------
     * tailwind-merge integration
     * --------------------------- */
    $merge = null;

    if (class_exists('\TailwindMerge\TailwindMerge')) {
        $merge = \TailwindMerge\TailwindMerge::instance();
    } elseif (class_exists('\TitasGailius\TailwindMerge\TailwindMerge')) {
        $merge = \TitasGailius\TailwindMerge\TailwindMerge::instance();
    } elseif (class_exists('\TailwindMerge')) {
        $merge = \TailwindMerge::instance();
    }

    // Callable used by both filter and function.
    $twmergeCallable = $merge
        ? static fn($classes) => $merge->merge($classes)
        : static fn($classes) => $classes; // no-op so Twig never errors

    $twig->addFilter(new \Twig\TwigFilter('twmerge', $twmergeCallable));
    $twig->addFunction(new \Twig\TwigFunction('twmerge', $twmergeCallable));

    /** ----------------------------
     * Lucide (SVG) integration
     * --------------------------- */
    static $icons = null;
    if ($icons === null && class_exists('\Lucide\IconManager')) {
        $icons = new \Lucide\IconManager();

        // Optional global defaults:
        // $icons->setSize(20);          // px
        // $icons->setColor('#111');     // stroke color
        // $icons->setWeight(1.5);       // stroke-width
        // $icons->addCssClass('icon');  // add a class to every icon
    }

    $twig->addFunction(new \Twig\TwigFunction(
        'lucide',
        function (string $name, array $attrs = [], ?string $alt = null) use ($icons, $merge) {
            if (!$icons) {
                return new \Twig\Markup('', 'UTF-8'); // library not installed
            }

            // Build the icon
            $icon = $icons->getIcon($name, [], $alt);

            // If a 'class' is provided and tailwind-merge is available, merge classes.
            if (isset($attrs['class']) && $merge instanceof \TailwindMerge\TailwindMerge) {
                $attrs['class'] = $merge->merge($attrs['class']);
            }

            // Apply attributes (class, stroke, stroke-width, width/height, data-*, aria-*, etc.)
            foreach ($attrs as $k => $v) {
                // Common nicety: allow size shorthand
                if ($k === 'size') {
                    $icon->setSize((int) $v);
                    continue;
                }
                $icon->setAttribute($k, $v);
            }

            // Return inline SVG as safe HTML for Twig
            return new \Twig\Markup((string) $icon, 'UTF-8');
        },
        ['is_safe' => ['html']]
    ));

    return $twig;
});