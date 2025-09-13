<?php

namespace App\Providers;

use Roots\Acorn\Sage\SageServiceProvider;

class WooCommerceServiceProvider extends SageServiceProvider
{
    public function register(): void
    {
        // Optional: bind singletons, config, etc. Only if WC is active.
        if (! class_exists('WooCommerce')) {
            return;
        }
        // Example bindings could go here.
    }

    public function boot(): void
    {
        // Don’t bootstrap anything unless WooCommerce is available.
        if (! class_exists('WooCommerce')) {
            return;
        }

        // If you need to be extra safe that WC finished booting:
        // add_action('woocommerce_init', fn () => $this->loadWooModules());
        $this->loadWooModules();
    }

    protected function loadWooModules(): void
    {
        $dir = get_theme_file_path('app/WooCommerce');

        // Require every PHP file in app/WooCommerce (the files you pasted)
        foreach (glob($dir . '/*.php') as $file) {
            require_once $file;
        }

        register_nav_menus([
            'checkout_footer' => __('Checkout Footer', 'wordpress-quickstart'),
        ]);
    }
}