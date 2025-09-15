@php
global $post;

$short_description = apply_filters( 'woocommerce_short_description', $post->post_excerpt );

if ( ! $short_description ) {
	return;
}
@endphp


<div class="woocommerce-product-details__short-description text-sm mt-2">
	<?php echo $short_description; // WPCS: XSS ok. ?>
</div>