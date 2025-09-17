{{-- resources/views/partials/account/merchandising.blade.php --}}

<div class="mt-10 space-y-8">
    @if(!empty($bestSellers))
        <section class="mt-8">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xl font-semibold">{!! __('Best sellers', 'woocommerce') !!}</h2>
                <a href="{{ $links['shop'] }}" class="btn btn-ghost btn-sm">{!! __('Browse shop', 'woocommerce') !!}
                    <x-lucide-store aria-hidden="true" />
                </a>

            </div>

            @php
                wc_set_loop_prop('columns', 6);
                echo woocommerce_product_loop_start(false);
              @endphp

            @foreach($bestSellers as $p)
                @php
                    global $product, $post;
                    $product = $p;
                    $post = get_post($p->get_id());
                    setup_postdata($post);
                    wc_get_template_part('content', 'product');
                @endphp
            @endforeach

            @php
                echo woocommerce_product_loop_end(false);
                wp_reset_postdata();
                wc_set_loop_prop('columns', null);
              @endphp
        </section>
    @endif
</div>