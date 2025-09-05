<?php

namespace App\View\Composers\WooCommerce\Loop;

use Roots\Acorn\View\Composer;

class Header extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'woocommerce.loop.header',
    ];

    /**
     * Retrieve the site name.
     */
    public function pageTitle(): string
    {
        return woocommerce_page_title(false);
    }
}
