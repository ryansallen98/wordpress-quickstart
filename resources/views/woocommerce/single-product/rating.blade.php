@php
    global $product;

    if (!wc_review_ratings_enabled()) {
        return;
    }

    $rating_count = $product->get_rating_count();
    $review_count = $product->get_review_count();
    $average = $product->get_average_rating();

    $icon = $icon ?? 'star';
    $iconSizeClass = $star_size ?? 'size-6';
    $iconClass = $star_class ?? 'h-6 w-6';
    $colorClass = $color_class ?? 'text-amber-500';
    $emptyClass = $empty_class ?? 'text-muted-foreground/50';

    $outlineComp = 'heroicon-o-' . $icon;
    $solidComp = 'heroicon-s-' . $icon;
@endphp

@if($rating_count > 0)
    <div class="flex items-center gap-2 flex-wrap mb-4">
        <div class="[&_svg:not([class*='size-'])]:{{ $iconSizeClass }} inline-flex items-center leading-none" role="img"
            aria-label="{{ $rating_label }}">
            @foreach (($rating_fills ?? []) as $fill)
                <span class="{{ $iconClass }} relative inline-flex items-center justify-center align-middle">
                    <x-dynamic-component :component="$outlineComp" class="absolute inset-0 {{ $iconClass }} {{ $emptyClass }}"
                        aria-hidden="true" />
                    @if ($fill > 0)
                        <span class="absolute top-0 left-0 h-full overflow-hidden" style="width: {{ $fill }}%">
                            <x-dynamic-component :component="$solidComp" class="{{ $iconClass }} {{ $colorClass }}"
                                aria-hidden="true" />
                        </span>
                    @endif
                </span>
            @endforeach

            <span class="sr-only">
                {{ sprintf(esc_html__('Rated %s out of 5', 'woocommerce'), $average) }}</span>
        </div>

        <?php    if (comments_open()): ?>
        <?php        //phpcs:disable ?>
        <a href="#reviews" class="woocommerce-review-link"
            rel="nofollow">(<?php        printf(_n('%s customer review', '%s customer reviews', $review_count, 'woocommerce'), '<span class="count">' . esc_html($review_count) . '</span>'); ?>)</a>
        <?php        // phpcs:enable ?>
        <?php    endif ?>
    </div>
@endif