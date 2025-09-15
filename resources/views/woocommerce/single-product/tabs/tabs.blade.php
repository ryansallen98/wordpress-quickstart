@php
    $product_tabs = apply_filters('woocommerce_product_tabs', array());
@endphp

@if(!empty($product_tabs))
    @foreach ($product_tabs as $key => $product_tab)
        @push('product-accordion-items')
            <x-accordion.item>
                <x-accordion.trigger>
                    {!! wp_kses_post(apply_filters('woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key)) !!}
                </x-accordion.trigger>
                <x-accordion.content>
                    <div class="woocommerce-Tabs-panel woocommerce-Tabs-panel--<?php        echo esc_attr($key); ?> panel entry-content wc-tab"
                        id="tab-<?php        echo esc_attr($key); ?>" role="tabpanel"
                        aria-labelledby="tab-title-<?php        echo esc_attr($key); ?>">
                        @php
                            if (isset($product_tab['callback'])) {
                                call_user_func($product_tab['callback'], $key, $product_tab);
                            }
                        @endphp
                    </div>
                </x-accordion.content>
            </x-accordion.item>
        @endpush
    @endforeach
@endif