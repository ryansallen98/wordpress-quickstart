@php
	/**
	 * Supplier products (My Account) — main template
	 *
	 * Copy to override:
	 * yourtheme/woocommerce/wc-supplier-manager/myaccount/supplier-products.php
	 *
	 * @var \WC_Product[] $products
	 * @var int           $user_id
	 * @var array         $controls
	 * @var array         $pagination
	 * @var string        $endpoint_url
	 */
	defined('ABSPATH') || exit;

	use WCSM\Support\TemplateLoader;

	$controls = $controls ?? [];
	$endpoint_url = $endpoint_url ?? wc_get_account_endpoint_url('supplier-products');

	// detect if any non-default filter is active
	$defaults = [
		'q' => '',
		'type' => 'all',
		'stock' => 'all',
		'orderby' => 'date',
		'order' => 'DESC',
		'per_page' => 10,
	];
	$active_filters = false;
	foreach ($defaults as $k => $def) {
		if (isset($controls[$k]) && (string) $controls[$k] !== (string) $def) {
			$active_filters = true;
			break;
		}
	}

	wc_print_notices();
@endphp

<div class="w-full overflow-x-hidden">
	<div class="border rounded-lg shadow-md bg-card">
		{{-- Filter form --}}
		@include('woocommerce.myaccount.partials.supplier.product.filter')

		@if (empty($products))
			<p>{{esc_html_e('No products match your current filters.', 'wc-supplier-manager')}}</p>
		@else
			<form method="post" action="{{esc_url($endpoint_url)}}">
				{!! wp_nonce_field('wcsm_sp_update') !!}
				<input type="hidden" name="wcsm_sp_action" value="update" />

				{{-- Table --}}
				@include('woocommerce.myaccount.partials.supplier.product.table')

				<p class="px-4 py-2">
					<button type="submit" class="button button-primary">
						{{esc_html_e('Apply changes', 'wc-supplier-manager')}}
					</button>
				</p>
			</form>

			@php
				TemplateLoader::get('myaccount/parts/pagination.php', [
					'pagination' => $pagination ?? [],
				]);
			@endphp
		@endif

		@php
			do_action('wcsm_after_supplier_products_table', $products, $user_id);
		@endphp
	</div>
</div>