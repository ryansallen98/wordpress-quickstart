<?php

namespace App\Providers;

use Roots\Acorn\Sage\SageServiceProvider;

class TgmpaServiceProvider extends SageServiceProvider
{
    public function register()
    {
        // Load the TGMPA class file
        require_once get_theme_file_path('app/Integrations/tgmpa/class-tgm-plugin-activation.php');
    }

    public function boot()
    {
        // Hook registration callback
        add_action('tgmpa_register', [$this, 'registerRequiredPlugins']);
    }

    public function registerRequiredPlugins(): void
    {
        $plugins = [
            [
                'name'     => 'Secure Custom Fields',
                'slug'     => 'secure-custom-fields',
                'required' => true,
            ],
            [
                'name'     => 'ACF Extended',
                'slug'     => 'acf-extended',
                'required' => true,
            ],
            [
                'name'     => 'Yoast SEO',
                'slug'     => 'wordpress-seo',
                'required' => false,
            ],
            [
                'name'     => 'ACF Content Analysis for Yoast SEO',
                'slug'     => 'acf-content-analysis-for-yoast-seo',
                'required' => false,
            ],
            [
                'name'     => 'Advanced Editor Tools (TinyMCE Advanced)',
                'slug'     => 'tinymce-advanced',
                'required' => false,
            ],
        ];

        $config = [
            'id'           => 'theme_name',
            'menu'         => 'tgmpa-install-plugins',
            'has_notices'  => true,
            'dismissable'  => false,
            'is_automatic' => true,
        ];

        // tgmpa() is provided by the TGMPA class file we required above
        tgmpa($plugins, $config);
    }
}