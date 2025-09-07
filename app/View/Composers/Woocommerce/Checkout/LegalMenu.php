<?php

namespace App\View\Composers\WooCommerce\Checkout;

use Roots\Acorn\View\Composer;
use Log1x\Navi\Navi;

class LegalMenu extends Composer
{
    protected static $views = ['woocommerce.checkout.*'];

    public function with(): array
    {
        $menu = app(Navi::class)->build('checkout_footer'); // theme location
        return [
            'menu' => $menu ? $menu->toArray() : [],
        ];
    }
}