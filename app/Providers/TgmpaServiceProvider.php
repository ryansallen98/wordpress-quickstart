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
                'name'     => 'Advanced Custom Fields PRO',
                'slug'     => 'advanced-custom-fields-pro',
                'required' => true,
            ],
            [
                'name'     => 'ACF Extended Pro',
                'slug'     => 'acf-extended-pro',
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
        ];

        $config = [
            'id'           => 'wordpress-quickstart',
            'menu'         => 'tgmpa-install-plugins',
            'has_notices'  => true,
            'dismissable'  => false,
            'is_automatic' => true,
        ];

        // tgmpa() is provided by the TGMPA class file we required above
        tgmpa($plugins, $config);
    }
}