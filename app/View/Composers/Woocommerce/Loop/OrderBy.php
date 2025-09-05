<?php

namespace App\View\Composers\WooCommerce\Loop;

use Roots\Acorn\View\Composer;

class OrderBy extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'woocommerce.loop.orderby',
    ];

    /**
     * Retrieve the select Id.
     */
    public function selectId(): string
    {
        $idSuffix = wp_unique_id();
        return "woocommerce-orderby-{$idSuffix}";
    }
}
