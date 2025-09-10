<table class="w-full text-sm border-collapse">
    <thead class="text-left font-medium bg-accent whitespace-nowrap">
        <tr>
            <th class="py-2 border-b"></th>
            <th class="py-2 px-4 border-b"><?php esc_html_e('Image', 'woocommerce'); ?></th>
            <th class="py-2 px-4 border-b"><?php esc_html_e('Product', 'woocommerce'); ?></th>
            <th class="py-2 px-4 border-b"><?php esc_html_e('SKU', 'woocommerce'); ?></th>
            <th class="py-2 px-4 border-b"><?php esc_html_e('Supplier price', 'wc-supplier-manager'); ?></th>
            <th class="py-2 px-4 border-b"><?php esc_html_e('Stock status', 'woocommerce'); ?></th>
            <th class="py-2 px-4 border-b"><?php esc_html_e('Backorders', 'woocommerce'); ?></th>
            <th class="py-2 px-4 border-b"><?php esc_html_e('Qty', 'woocommerce'); ?></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($products as $product)
            @include('woocommerce.myaccount.partials.supplier.product.row')
            @if($product->is_type('variable'))
                @foreach ($product->get_children() as $vid)
                    @php $variation = wc_get_product($vid); @endphp
                    @continue(!$variation || !$variation->exists())

                    @include('woocommerce.myaccount.partials.supplier.product.sub-row', [
                        'product' => $product,
                        'variation' => $variation,
                    ])
                @endforeach
            @endif
        @endforeach
    </tbody>
</table>