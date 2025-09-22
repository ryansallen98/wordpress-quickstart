@php
    /**
     * Render a Woo product card (content-product.php) and return HTML.
     * Ensures global $product and $post context is correct for Woo templates.
     */
    $renderProductCard = function (\WC_Product $p): string {
        if (!$p || !$p->is_visible())
            return '';

        global $product;
        $orig_product = isset($product) ? $product : null;
        $orig_post = $GLOBALS['post'] ?? null;

        $product = $p;
        $GLOBALS['post'] = get_post($p->get_id());
        if ($GLOBALS['post'])
            setup_postdata($GLOBALS['post']);

        ob_start();
        wc_get_template_part('content', 'product');
        $html = ob_get_clean();

        if ($orig_post) {
            $GLOBALS['post'] = $orig_post;
            setup_postdata($GLOBALS['post']);
        } else {
            wp_reset_postdata();
        }
        if ($orig_product !== null) {
            $product = $orig_product;
        }

        return $html;
    };

    $cardsFromProducts = function (array $products) use ($renderProductCard): array {
        $out = [];
        foreach ($products as $p) {
            if ($p instanceof \WC_Product && $p->is_visible()) {
                $out[] = $renderProductCard($p);
            }
        }
        return $out;
    };
@endphp

<div class="flex flex-col">
    <div class="container px-4 mx-auto relative -mb-[1px]">
        <div class="flex items-center gap-4 sm:gap-8 pt-8 flex-col sm:flex-row">

            <div class="flex items-center w-full sm:w-fit overflow-hidden">
                <div class="w-25 h-25 -mr-8 -mb-4 z-1">
                    {!! file_get_contents(get_theme_file_path('resources/images/svg/confetti.svg')) !!}
                </div>
                <div
                    class="flex items-center gap-4 bg-accent p-4 border rounded-t-xl border-b-0 mx-auto sm:mx-0 relative z-2">
                    <div class="h-20 w-20 rounded-md overflow-hidden flex items-center justify-center shadow bg-white">
                        {!! $purchasedImageHtml ?: '<span class="text-xs text-muted-foreground px-2 text-center">' . esc_html__('No image', 'wordpress-quickstart') . '</span>' !!}
                    </div>

                    <div>
                        <x-lucide-plus-circle class="h-4 w-4 text-primary" />
                    </div>

                    <div
                        class="h-20 w-20 border-2 border-primary border-dashed bg-primary/10 flex items-center justify-center flex-col text-center text-xs p-2 rounded-md shadow">
                        <span
                            class="font-medium text-primary">{{ __('Choose an Extra Below!', 'wordpress-quickstart') }}</span>
                    </div>
                </div>
                <div class="w-25 h-25 -ml-8 z-1 -mb-4" style="transform: scaleX(-1);">
                    {!! file_get_contents(get_theme_file_path('resources/images/svg/confetti.svg')) !!}
                </div>
            </div>

            @php
                $title = $product->is_type('variation')
                    ? wc_get_product($product->get_parent_id())->get_name()
                    : $product->get_name();
            @endphp

            <h1 class="text-2xl md:text-3xl font-medium tracking-tight text-center sm:text-left flex-1 order-first sm:order-none">
                {{ sprintf(__('Pair your %s with a little extra', 'wordpress-quickstart'), $title) }}
            </h1>
        </div>
    </div>

    @if(!empty($crossSellProducts))
        @php
            $xsCards = $cardsFromProducts($crossSellProducts);
            $xsCount = count($xsCards);
        @endphp
        <div class="bg-accent py-8 border-y">
            <div class="container px-4 mx-auto">
                <h2 class="mb-4 text-2xl font-medium tracking-tight">
                    {!! __('We think these products will complement your purchase:', 'wordpress-quickstart') !!}
                </h2>

                @if($xsCount > 0)
                    <x-carousel>
                        <x-carousel.container>
                            @foreach($xsCards as $html)
                                <x-carousel.item class="basis-1/3 sm:basis-1/6 mr-8">
                                    {!! $html !!}
                                </x-carousel.item>
                            @endforeach
                        </x-carousel.container>

                        @if($xsCount > 5)
                            <x-carousel.prev class="btn btn-outline btn-lg sm:flex hidden" />
                            <x-carousel.next class="btn btn-outline btn-lg sm:flex hidden" />
                        @endif
                    </x-carousel>
                @else
                    <p class="text-sm text-muted-foreground">{{ __('No items', 'wordpress-quickstart') }}</p>
                @endif
            </div>
        </div>
    @endif

    @if(!empty($relatedProducts))
        @php
            $relCards = $cardsFromProducts($relatedProducts);
            $relCount = count($relCards);
        @endphp
        <div class="py-8 border-b-2 border-dashed">
            <div class="container px-4 mx-auto">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-medium tracking-tight">{{ __('Related products', 'wordpress-quickstart') }}
                    </h2>
                    <a class="btn btn-ghost btn-sm"
                        href="{{ function_exists('wc_get_page_permalink') ? esc_url(wc_get_page_permalink('shop')) : esc_url(home_url('/shop')) }}">
                        {{ __('View All', 'wordpress-quickstart') }}
                    </a>
                </div>

                @if($relCount > 0)
                    <x-carousel>
                        <x-carousel.container>
                            @foreach($relCards as $html)
                                <x-carousel.item class="basis-1/3 sm:basis-1/6 mr-8">
                                    {!! $html !!}
                                </x-carousel.item>
                            @endforeach
                        </x-carousel.container>

                        @if($relCount > 5)
                            <x-carousel.prev class="btn btn-outline btn-lg sm:flex hidden" />
                            <x-carousel.next class="btn btn-outline btn-lg sm:flex hidden" />
                        @endif
                    </x-carousel>
                @else
                    <p class="text-sm text-muted-foreground">{{ __('No items', 'wordpress-quickstart') }}</p>
                @endif
            </div>
        </div>
    @endif

    <div>
        @if(!empty($categoryLoops))
            @foreach($categoryLoops as $block)
                @php
                    $catCards = $cardsFromProducts($block['products'] ?? []);
                    $catCount = count($catCards);
                  @endphp

                <div class="py-8 space-y-12 border-b-2 border-dashed last:border-b-0!">
                    <div class="container px-4 mx-auto">
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-2xl font-medium tracking-tight">
                                    {{ sprintf(__('Explore our %s', 'wordpress-quickstart'), $block['title']) }}
                                </h2>
                                <a href="{{ $block['url'] }}" class="btn btn-ghost btn-sm">
                                    {{ __('View All', 'wordpress-quickstart') }}
                                </a>
                            </div>

                            @if($catCount > 0)
                                <x-carousel>
                                    <x-carousel.container>
                                        @foreach($catCards as $html)
                                            <x-carousel.item class="basis-1/3 sm:basis-1/6 mr-8">
                                                {!! $html !!}
                                            </x-carousel.item>
                                        @endforeach
                                    </x-carousel.container>

                                    @if($catCount > 5)
                                        <x-carousel.prev class="btn btn-outline btn-lg sm:flex hidden" />
                                        <x-carousel.next class="btn btn-outline btn-lg sm:flex hidden" />
                                    @endif
                                </x-carousel>
                            @else
                                <p class="text-sm text-muted-foreground">{{ __('No items', 'wordpress-quickstart') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <div class="mx-auto my-8 mb-16 flex flex-col sm:flex-row gap-3 w-full sm:w-fit px-4">
        <a href="{!! esc_url($continueUrl) !!}" class="btn btn-ghost btn-lg w-full sm:w-fit">
            <x-lucide-store aria-hidden="true" /> {{ __('Continue Shopping', 'wordpress-quickstart') }}
        </a>
        <a href="{!! esc_url($cartUrl) !!}" class="btn btn-outline btn-lg w-full sm:w-fit">
            <x-lucide-shopping-cart aria-hidden="true" /> {{ __('View Cart', 'wordpress-quickstart') }}
        </a>
        <a href="{!! esc_url($checkoutUrl) !!}" class="btn btn-primary btn-lg w-full sm:w-fit" data-cs-checkout="1">
            <x-heroicon-s-lock-closed aria-hidden="true" /> {{ __('Checkout', 'wordpress-quickstart') }}
        </a>
    </div>
</div>