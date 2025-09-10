<?php

namespace App\View\Composers\WooCommerce\MyAccount;

use Roots\Acorn\View\Composer;

class MyAccount extends Composer
{
    protected static $views = [
        'woocommerce.myaccount.*',
    ];

    public function with(): array
    {
        return [
            'title' => $this->get_current_my_account_tab_title(),
        ];
    }

    protected function get_current_my_account_tab_title(): string
    {
        global $wp;

        // List of My Account endpoints
        $endpoints = wc_get_account_menu_items();

        // Default title (the main My Account page)
        $title = get_the_title(wc_get_page_id('myaccount'));

        foreach ($endpoints as $endpoint => $label) {
            if (isset($wp->query_vars[$endpoint])) {
                $title = $label;
                break;
            }
        }

        return $title;
    }
}