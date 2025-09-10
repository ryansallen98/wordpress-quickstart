@php
    use function WCSM\Support\wcsm_get_supplier_price_for_product;

    $thumb = $product->get_image('thumbnail');
    $name = $product->get_name();
    $sku = $product->get_sku();
    $reg = $product->get_regular_price();
    $supp = wcsm_get_supplier_price_for_product($product);
    $is_variable = $product->is_type('variable');
    $product_id = $product->get_id();
@endphp

<tr class="wcsm-parent" data-product="{{ esc_attr($product_id) }}">
    @if (!$is_variable)
        <!-- Only POST parent IDs when the parent is a SIMPLE product -->
        <input type="hidden" name="wcsm_ids[]" value="{{ esc_attr($product_id) }}" />
    @endif

    <td class="pl-4 py-2 border-b">
        @if ($is_variable)
            <button type="button" class="btn btn-outline btn-icon wcsm-toggle group" aria-expanded="false"
                data-product="<?php    echo esc_attr($product_id); ?>"
                title="<?php    esc_attr_e('Toggle variations', 'wc-supplier-manager'); ?>">
                <x-lucide-plus aria-hidden="true" class="group-aria-expanded:hidden block" />
                <x-lucide-minus aria-hidden="true" class="group-aria-expanded:block hidden" />
                <span class="screen-reader-text"><?php    esc_html_e('Toggle variations', 'wc-supplier-manager'); ?></span>
            </button>
        @endif
    </td>
    <td class="px-4 py-2 border-b">
        <div class="w-10 h-10 rounded-md shadow-sm overflow-hidden">
            {!! wp_kses_post($thumb) !!}
        </div>
    </td>

    <td class="px-4 py-2 border-b">
        <strong><?php echo esc_html($name); ?></strong>
    </td>

    <td class="px-4 py-2 border-b"><?php echo $sku ? esc_html($sku) : '&mdash;'; ?></td>
    <td class="px-4 py-2 border-b"><?php echo $supp !== '' ? wc_price($supp) : '&mdash;'; ?></td>

    <?php if ($is_variable): ?>
    <!-- Variable parent: controls are managed at variation level -->
    <td class="px-4 py-2 border-b"><span>&mdash;</span></td>
    <td class="px-4 py-2 border-b"><span>&mdash;</span></td>
    <td class="px-4 py-2 border-b"><span>&mdash;</span></td>
    <?php else: ?>
    <!-- SIMPLE parent: show editable controls -->
    <td class="px-4 py-2 border-b">
        <?php    $ss = $product->get_stock_status(); ?>
        <select class="input-select" name="wcsm_stock_status[<?php    echo esc_attr($product_id); ?>]">
            <?php
    $opts = [
        'instock' => __('In stock', 'woocommerce'),
        'outofstock' => __('Out of stock', 'woocommerce'),
        'onbackorder' => __('On backorder', 'woocommerce'),
    ];
    foreach ($opts as $val => $label) {
        printf('<option value="%s"%s>%s</option>', esc_attr($val), selected($ss, $val, false), esc_html($label));
    }
				?>
        </select>
    </td>

    <td class="px-4 py-2 border-b">
        <?php    $bo = $product->get_backorders(); ?>
        <select class="input-select" name="wcsm_backorders[<?php    echo esc_attr($product_id); ?>]">
            <?php
    $bo_opts = [
        'no' => __('Do not allow', 'woocommerce'),
        'notify' => __('Allow, but notify customer', 'woocommerce'),
        'yes' => __('Allow', 'woocommerce'),
    ];
    foreach ($bo_opts as $val => $label) {
        printf('<option value="%s"%s>%s</option>', esc_attr($val), selected($bo, $val, false), esc_html($label));
    }
				?>
        </select>
    </td>

    <td class="px-4 py-2 border-b">
        <?php    if ($product->managing_stock()): ?>
        <?php        $q = $product->get_stock_quantity(); ?>
        <input class="input-text" type="number" step="1" min="0" name="wcsm_qty[<?php        echo esc_attr($product_id); ?>]"
            value="<?php        echo esc_attr(null === $q ? '' : $q); ?>" style="width:80px;" />
        <?php    else: ?>
        <span>&mdash;</span>
        <?php    endif; ?>
    </td>
    <?php endif; ?>
</tr>