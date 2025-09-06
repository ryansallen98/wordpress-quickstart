<?php

namespace App\View\Composers\WooCommerce\Checkout;

use Roots\Acorn\View\Composer;

class FormCheckout extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'woocommerce.checkout.form-checkout'
    ];

    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with()
    {
        return [
            'returnUrl'  => wc_get_cart_url(),
            'returnText' => __('Back to cart', 'wordpress-quickstart'), 
        ];
    }
}