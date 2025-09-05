<?php

namespace App\View\Composers\WooCommerce\Loop;

use Roots\Acorn\View\Composer;

class AddToCart extends Composer
{
    protected static $views = [
        'woocommerce.loop.add-to-cart',
    ];

    public function with(): array
    {
        global $product;
    
        // Get product data
        $id = $product ? $product->get_id() : '';
        $label = $product ? $product->add_to_cart_text() : '';
        $href = $product ? $product->add_to_cart_url() : '';
        $target = $product ? $product->get_type() === 'external' ? '_blank' : '_self' : '';
        $type = $product ? $product->get_type() : '';

        return [
            'id' => $id,
            'label' => $label,
            'href' => $href,
            'type' => $type,
            'target' => $target,
            'product' => $product,
        ];
    }
}