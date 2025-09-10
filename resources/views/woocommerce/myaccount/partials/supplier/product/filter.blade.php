@php
    $c = $controls ?? [];
    $endpoint_url = $endpoint_url ?? wc_get_account_endpoint_url('supplier-products');

    $orderby_opts = [
        'date' => __('Date', 'woocommerce'),
        'title' => __('Name', 'woocommerce'),
        'price' => __('Price', 'woocommerce'),
        'sku' => __('SKU', 'woocommerce'),
        'stock' => __('Stock status', 'woocommerce'),
    ];
    $type_opts = [
        'all' => __('All types', 'woocommerce'),
        'simple' => __('Simple', 'woocommerce'),
        'variable' => __('Variable', 'woocommerce'),
    ];
    $stock_opts = [
        'all' => __('All stock', 'woocommerce'),
        'instock' => __('In stock', 'woocommerce'),
        'outofstock' => __('Out of stock', 'woocommerce'),
    ];
@endphp

<div class="flex flex-row gap-4 w-full items-end border-b py-2 px-4 overflow-auto whitespace-nowrap">
    <form class="wcsm-controls flex flex-row gap-4 w-full items-end" method="get"
        action="<?php echo esc_url($endpoint_url); ?>">
        <div class="min-w-[180px]">
            <label class="input-label">
                {{ esc_html__('Status', 'wc-supplier-manager') }}
            </label>
            <input type="search" name="q" value="<?php echo esc_attr($c['q'] ?? ''); ?>"
                placeholder="<?php esc_attr_e('Search products…', 'woocommerce'); ?>" class="input-text" />
        </div>

        <div>
            <label class="input-label">
                {{ esc_html__('Product Type', 'wc-supplier-manager') }}
            </label>
            <select name="type" class="input-select">
                <?php foreach ($type_opts as $val => $label): ?>
                <option value="<?php    echo esc_attr($val); ?>" <?php    selected($c['type'] ?? 'all', $val); ?>>
                    <?php    echo esc_html($label); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="input-label">
                {{ esc_html__('Stock Status', 'wc-supplier-manager') }}
            </label>
            <select name="stock" class="input-select">
                <?php foreach ($stock_opts as $val => $label): ?>
                <option value="<?php    echo esc_attr($val); ?>" <?php    selected($c['stock'] ?? 'all', $val); ?>>
                    <?php    echo esc_html($label); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="input-label">
                {{ esc_html__('Sort By', 'wc-supplier-manager') }}
            </label>
            <select name="orderby" class="input-select">
                <?php foreach ($orderby_opts as $val => $label): ?>
                <option value="<?php    echo esc_attr($val); ?>" <?php    selected($c['orderby'] ?? 'date', $val); ?>>
                    <?php    echo esc_html($label); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="input-label">
                {{ esc_html__('Order', 'wc-supplier-manager') }}
            </label>
            <select name="order" class="input-select">
                <option value="DESC" <?php selected($c['order'] ?? 'DESC', 'DESC'); ?>>
                    <?php esc_html_e('Desc', 'woocommerce'); ?>
                </option>
                <option value="ASC" <?php selected($c['order'] ?? 'DESC', 'ASC'); ?>>
                    <?php esc_html_e('Asc', 'woocommerce'); ?>
                </option>
            </select>
        </div>

        <div class="ml-auto min-w-[120px]">
            <label class="input-label">
                {{ esc_html__('Per page', 'wc-supplier-manager') }}
            </label>
            <select name="per_page" class="input-select">
                <?php foreach ([10, 20, 30, 50] as $pp): ?>
                <option value="<?php    echo esc_attr($pp); ?>" <?php    selected((int) ($c['per_page'] ?? 10), $pp); ?>>
                    <?php    echo esc_html($pp); ?>/page
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-outline">
            <x-lucide-filter />
            <?php esc_html_e('Apply', 'woocommerce'); ?>
        </button>
    </form>

    @if($active_filters)
        <p>
            <a class="btn btn-primary" href="<?php    echo esc_url($endpoint_url); ?>">
                <x-lucide-x-circle />
                <?php    esc_html_e('Reset filters', 'wc-supplier-manager'); ?>
            </a>
        </p>
    @endif
</div>