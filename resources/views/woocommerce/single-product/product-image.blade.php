@if (!empty($can_render))
    <div class="{{ $wrapper_class_attr }} w-full" data-columns="{{ esc_attr($columns) }}"
        style="opacity:0;transition:opacity .25s ease-in-out;">

        <x-carousel x-on:wc-variation-change.window="to(0); resetAutoplay?.()" :draggable="false" lazy>
            <div class="shadow-lg rounded-lg overflow-hidden">
                <x-carousel.container class="w-full">
                    {{-- PRIMARY --}}
                    <x-carousel.item class="shrink-0 basis-full">
                        <div class="woocommerce-product-gallery__image relative w-full aspect-square overflow-hidden">
                            @if ($has_images && $primary)
                                <figure class="absolute inset-0 !mb-0" data-large_image="{{ $primary['data']['large_image'] }}"
                                    data-large_image_width="{{ $primary['data']['large_image_width'] }}"
                                    data-large_image_height="{{ $primary['data']['large_image_height'] }}">
                                    <img class="wp-post-image js-wc-main-image block absolute inset-0 w-full h-full object-cover object-center bg-gradient-to-t from-stone-100 dark:from-accent to-bg-background dark:to-accent/50"
                                        src="{{ $primary['src'] }}" width="{{ $primary['width'] }}"
                                        height="{{ $primary['height'] }}" alt="{{ $primary['alt'] }}" @if($primary['srcset'])
                                        srcset="{{ $primary['srcset'] }}" @endif @if($primary['sizes'])
                                        sizes="{{ $primary['sizes'] }}" @endif loading="eager" decoding="async">
                                </figure>
                            @else
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <img class="block max-h-full max-w-full" src="{{ $placeholder['src'] }}"
                                        alt="{{ $placeholder['alt'] }}">
                                </div>
                            @endif
                        </div>
                    </x-carousel.item>

                    {{-- GALLERY --}}
                    @foreach($gallery as $image)
                        <x-carousel.item class="shrink-0 basis-full" lazy>
                            <div class="relative w-full aspect-square overflow-hidden"
                                data-large_image="{{ $image['data']['large_image'] }}"
                                data-large_image_width="{{ $image['data']['large_image_width'] }}"
                                data-large_image_height="{{ $image['data']['large_image_height'] }}">
                                <img class="lazy-load block absolute inset-0 w-full h-full object-cover object-center"
                                    src="{{ $image['src'] }}" width="{{ $image['width'] }}" height="{{ $image['height'] }}"
                                    alt="{{ $image['alt'] }}" @if($image['srcset']) srcset="{{ $image['srcset'] }}" @endif
                                    @if($image['sizes']) sizes="{{ $image['sizes'] }}" @endif loading="lazy" decoding="async">
                            </div>
                        </x-carousel.item>
                    @endforeach
                </x-carousel.container>
            </div>

            @if(count($thumbnails) > 1)
                <x-carousel.thumbnails :items="$thumbnails" size="w-16 h-16" class="px-2" />
            @endif
        </x-carousel>
    </div>
@endif

@pushOnce('scripts')
<script>
    jQuery(function ($) {
        // Target only the product’s own form → dispatch a global event (keeps Alpine decoupled from jQuery)
        $(document).on('found_variation reset_data hide_variation', '.variations_form', function () {
            window.dispatchEvent(new CustomEvent('wc-variation-change'));
        });
    });
</script>
@endpushOnce