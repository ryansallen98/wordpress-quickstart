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
            'returnUrl' => wc_get_cart_url(),
            'returnText' => __('Back to cart', 'wordpress-quickstart'),
            'should_show_login' => $this->shouldShowLogin(),
        ];
    }

    protected function shouldShowLogin(): bool
    {
        // If already logged in, don’t show the login toggle
        if (is_user_logged_in()) {
            return false;
        }

        // Did the user try to log in (form submit)?
        $postedLogin = isset($_POST['login']) // wp-login form button name
            || (isset($_POST['username']) && isset($_POST['password']));

        // Woo errors (e.g., bad credentials)
        $hasWooErrors = function_exists('wc_notice_count') && wc_notice_count('error') > 0;

        // Manual trigger via query param (?showlogin=1)
        $showLoginQuery = !empty($_GET['showlogin']);

        // Guest checkout disabled in settings
        $guestDisabled = (get_option('woocommerce_enable_guest_checkout') === 'no');

        return $postedLogin || $hasWooErrors || $showLoginQuery || $guestDisabled;
    }
}