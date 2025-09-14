@php
    global $product;

    $days = 30;
    $is_new = false;

    if ($product) {
        $post_date = strtotime($product->get_date_created());
        if ($post_date >= strtotime("-{$days} days")) {
            $is_new = true;
        }
    }
@endphp

<div class="absolute z-10 -right-4 top-2 flex flex-col gap-1">

    {{-- New Badge --}}
    @if($is_new)
        <div class="flex flex-col items-end">
            <div class="bg-blue-500 shadow-sm px-8 py-1 uppercase text-white font-bold">
                {{ esc_html__('New!', 'wordpress-quickstart') }}
            </div>

            <div class="h-4 w-4 bg-blue-700 shadow-sm [clip-path:polygon(0_0,100%_0,0_100%)]"></div>
        </div>
    @endif

    {{-- Sale Badge --}}
    @if($product && $product->is_on_sale())
        <div class="flex flex-col items-end">
            <div class="bg-red-500 shadow-sm px-8 py-1 uppercase text-white font-bold">
                {{ esc_html__('Sale!', 'woocommerce') }}
            </div>

            <div class="h-4 w-4 bg-red-700 shadow-sm [clip-path:polygon(0_0,100%_0,0_100%)]"></div>
        </div>
    @endif
</div>