<?php

// Initialize context array
$context = [];

// Populate context with subfield values
if (!empty($layout['sub_fields'])) {
    foreach ($layout['sub_fields'] as $subfield) {
        $name = $subfield['name'];
        $context[$name] = get_sub_field($name);
    }
}

// Get the layout name
$name = $layout['name'];

// Render the template with the context
Timber::render(get_template_directory() . '/layouts/sections/' . $name . '.twig', $context);