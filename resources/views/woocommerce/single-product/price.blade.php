@php
global $product;
@endphp

<p class="<?php echo esc_attr( apply_filters( 'woocommerce_product_price_class', 'price' ) ); ?> text-xl font-medium"><?php echo $product->get_price_html(); ?></p>
