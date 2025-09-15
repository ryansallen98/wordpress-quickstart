@php
global $product;
@endphp

<div class="prose prose-sm">
@php do_action( 'woocommerce_product_additional_information', $product ); @endphp
</div>