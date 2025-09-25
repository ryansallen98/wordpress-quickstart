<a href="{{ esc_url(wc_get_checkout_url()) }}"
	class="checkout-button btn btn-primary btn-lg w-full alt wc-forward{{ wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : '' }}">
	<x-heroicon-s-lock-closed aria-hidden="true" />
	{{ __('Proceed to checkout', 'woocommerce') }}
</a>