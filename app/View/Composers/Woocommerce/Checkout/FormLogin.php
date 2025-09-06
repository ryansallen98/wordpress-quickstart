<?php

namespace App\View\Composers\WooCommerce\Checkout;

use Roots\Acorn\View\Composer;

class FormLogin extends Composer
{
    /**
     * The views that this composer applies to.
     */
    protected static $views = [
        'woocommerce.checkout.form-login',
        'woocommerce.checkout.form-checkout',
    ];

    /**
     * Data to be passed to the view.
     */
    public function with(): array
    {
        return [
            'showLogin'       => ! is_user_logged_in() && 'yes' === get_option('woocommerce_enable_checkout_login_reminder') &&  \WC_Checkout::instance()->is_registration_enabled(),
            'message'         => apply_filters('woocommerce_checkout_login_message', __('Returning customer?', 'woocommerce')),
            'checkoutUrl'     => function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : '',
            'lostPasswordUrl' => wp_lostpassword_url(),
            'myAccountUrl'    => function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account'),
        ];
    }
}