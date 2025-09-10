<table class="w-full text-sm border-collapse">
    <thead class="text-left font-medium bg-accent">
        <tr>
            <th class="py-2 px-4 border-b"><?php esc_html_e('Order', 'woocommerce'); ?></th>
            <th class="py-2 px-4 border-b"><?php esc_html_e('Date', 'woocommerce'); ?></th>
            <th class="py-2 px-4 border-b"><?php esc_html_e('Customer', 'woocommerce'); ?></th>
            <th class="py-2 px-4 border-b"><?php esc_html_e('Items', 'wc-supplier-manager'); ?></th>
            <th class="py-2 px-4 border-b"><?php esc_html_e('Status', 'wc-supplier-manager'); ?></th>
            <th class="py-2 px-4 border-b text-right"><?php esc_html_e('Actions', 'woocommerce'); ?></th>
        </tr>
    </thead>
    <tbody>

        @foreach ($orders as $order)
            @include('woocommerce.myaccount.partials.supplier.order.row', ['order' => $order, 'supplier_id' => $supplier_id])
        @endforeach
    </tbody>
</table>