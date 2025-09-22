@php
    /**
     * Render a Woo product card (content-product.php) and return HTML.
     * (Same helper you already use elsewhere)
     */
    $renderProductCard = function (\WC_Product $p): string {
        if (!$p || !$p->is_visible()) return '';
        global $product;
        $orig_product = isset($product) ? $product : null;
        $orig_post    = $GLOBALS['post'] ?? null;

        $product         = $p;
        $GLOBALS['post'] = get_post($p->get_id());
        if ($GLOBALS['post']) setup_postdata($GLOBALS['post']);

        ob_start();
        wc_get_template_part('content', 'product');
        $html = ob_get_clean();

        if ($orig_post) { $GLOBALS['post'] = $orig_post; setup_postdata($GLOBALS['post']); }
        else { wp_reset_postdata(); }
        if ($orig_product !== null) { $product = $orig_product; }

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

    // Build cross-sell cards from what the composer provides:
    $xsCards = $cardsFromProducts($crossSellProducts ?? []);
    $xsCount = count($xsCards);
@endphp

<div class="bg-accent py-8 border-y">
  <div class="container px-4 mx-auto">
    <h1 class="mb-1 text-3xl font-medium tracking-tight">
      {!! __('Fancy anything else before you check out?', 'wordpress-quickstart') !!}
    </h1>
    <p class="mb-8 text-lg tracking-tight">
      {!! __('We think these products pair nicely with your selection', 'wordpress-quickstart') !!}
    </p>

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
                {{ sprintf(__('Have you seen our %s', 'wordpress-quickstart'), $block['title']) }}
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
  <a href="{{ esc_url(wc_get_page_permalink('shop')) }}" class="btn btn-outline btn-lg w-full sm:w-fit">
    <x-lucide-store aria-hidden="true" />
    {{ __('Continue shopping', 'your-textdomain') }}
  </a>
  <a href="{!! esc_url($checkout_url) !!}" class="btn btn-primary btn-lg w-full sm:w-fit">
    <x-heroicon-s-lock-closed aria-hidden="true" />
    {{ __('Continue to checkout', 'your-textdomain') }}
  </a>
</div>