<?php

// Exit if accessed directly.
if (!defined("ABSPATH")) {
    return;
}

require_once get_template_directory() .
    "/includes/integrations/class-tgm-plugin-activation.php";

add_action("tgmpa_register", "theme_name_register_required_plugins");

function theme_name_register_required_plugins()
{
    $plugins = [
        [
            "name" => "Secure Custom Fields",
            "slug" => "secure-custom-fields",
            "required" => true,
        ],
        [
            'name' => 'ACF Extended',
            'slug' => 'acf-extended',
            'required' => true,
        ],
        [
            'name' => 'Yoast SEO',
            'slug' => 'wordpress-seo',
            'required' => false,
        ],
        [
            'name' => 'ACF Content Analysis for Yoast SEO',
            'slug' => 'acf-content-analysis-for-yoast-seo',
            'required' => false,
        ],
        [
            'name' => 'Advanced Editor Tools (TinyMCE Advanced)',
            'slug' => 'tinymce-advanced',
            'required' => false,
        ],
    ];

    $config = [
        "id" => "theme_name",
        "menu" => "tgmpa-install-plugins",
        "has_notices" => true,
        "dismissable" => false,
        "is_automatic" => true,
    ];

    tgmpa($plugins, $config);
}
