@php
use Automattic\WooCommerce\Enums\ProductType;
global $product;
@endphp

@push('product-accordion-items')
    <x-accordion.item>
        <x-accordion.trigger>
            {{ __('Product Details', 'woocommerce') }}
        </x-accordion.trigger>
        <x-accordion.content>
            <div class="product_meta flex flex-col gap-2">

                <?php do_action('woocommerce_product_meta_start'); ?>

                <?php if (wc_product_sku_enabled() && ($product->get_sku() || $product->is_type(ProductType::VARIABLE))): ?>

                <span class="sku_wrapper"><?php    esc_html_e('SKU:', 'woocommerce'); ?> <span
                        class="sku"><?php    echo ($sku = $product->get_sku()) ? $sku : esc_html__('N/A', 'woocommerce'); ?></span></span>

                <?php endif; ?>

                <?php echo wc_get_product_category_list($product->get_id(), ', ', '<span class="posted_in">' . _n('Category:', 'Categories:', count($product->get_category_ids()), 'woocommerce') . ' ', '</span>'); ?>

                <?php echo wc_get_product_tag_list($product->get_id(), ', ', '<span class="tagged_as">' . _n('Tag:', 'Tags:', count($product->get_tag_ids()), 'woocommerce') . ' ', '</span>'); ?>

                <?php do_action('woocommerce_product_meta_end'); ?>

            </div>
        </x-accordion.content>
    </x-accordion.item>
@endpush