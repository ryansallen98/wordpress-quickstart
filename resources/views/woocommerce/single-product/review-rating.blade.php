{{--
 * Reviewer's star rating (Blade version)
 * Copy to: resources/views/woocommerce/review-rating.blade.php
 * Replaces wc_get_rating_html() with Tailwind/heroicons-based stars
 * Based on WooCommerce template version 3.6.0
--}}

@php
if (!defined('ABSPATH')) { exit; }

// WooCommerce provides the $comment global for each review item
/** @var WP_Comment $comment */
global $comment;
$raw_rating = get_comment_meta($comment->comment_ID, 'rating', true);
$rating = is_numeric($raw_rating) ? (float) $raw_rating : 0.0;
@endphp

@if($rating && wc_review_ratings_enabled())
    @php
        // Customisation knobs (accept overrides if passed in when including the template)
        $icon           = $icon            ?? 'star';
        $iconSizeClass  = $star_size       ?? 'size-3';
        $iconClass      = $star_class      ?? 'h-3 w-3';
        $colorClass     = $color_class     ?? 'text-amber-500';
        $emptyClass     = $empty_class     ?? 'text-muted-foreground/50';
        $outlineComp    = 'heroicon-o-' . $icon;
        $solidComp      = 'heroicon-s-' . $icon;

        // Build fills for 5 stars (supports fractional ratings just in case)
        $fills = [];
        $remaining = max(0.0, min(5.0, $rating));
        for ($i = 0; $i < 5; $i++) {
            $fill = max(0.0, min(1.0, $remaining));
            $fills[] = round($fill * 100, 2); // percentage width
            $remaining -= 1.0;
        }

        $aria_label = sprintf(esc_html__('Rated %s out of 5', 'woocommerce'), esc_html($rating));
    @endphp

    <div class="inline-flex items-center leading-none [&_svg:not([class*='size-'])]:{{ $iconSizeClass }}" role="img" aria-label="{{ $aria_label }}">
        @foreach ($fills as $fill)
            <span class="{{ $iconClass }} relative inline-flex items-center justify-center align-middle">
                <x-dynamic-component :component="$outlineComp" class="absolute inset-0 {{ $iconClass }} {{ $emptyClass }}" aria-hidden="true" />
                @if ($fill > 0)
                    <span class="absolute top-0 left-0 h-full overflow-hidden" style="width: {{ $fill }}%">
                        <x-dynamic-component :component="$solidComp" class="{{ $iconClass }} {{ $colorClass }}" aria-hidden="true" />
                    </span>
                @endif
            </span>
        @endforeach
        <span class="sr-only">{{ $aria_label }}</span>
    </div>
@endif