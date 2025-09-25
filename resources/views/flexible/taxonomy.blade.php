@php
    $taxonomyInput = $context['taxonomy'] ?? null;

    // Normalize to name + label regardless of object/array shape
    $taxonomyName = null;
    $allItemsLabel = 'Items';

    if ($taxonomyInput instanceof \WP_Taxonomy) {
        $taxonomyName = $taxonomyInput->name;
        $allItemsLabel = $taxonomyInput->labels->all_items
            ?? $taxonomyInput->label
            ?? ucfirst($taxonomyInput->name);
    } elseif (is_array($taxonomyInput)) {
        $taxonomyName = $taxonomyInput['name'] ?? null;
        $labels = $taxonomyInput['labels'] ?? [];
        $allItemsLabel = $labels['all_items']
            ?? ($taxonomyInput['label'] ?? ($taxonomyName ? ucfirst($taxonomyName) : 'Items'));
    }

    $terms = [];
    if ($taxonomyName) {
        $maybeTerms = get_terms([
            'taxonomy' => $taxonomyName,
            'hide_empty' => false,
        ]);
        $terms = is_wp_error($maybeTerms) ? [] : $maybeTerms;
    }
@endphp

@if(!empty($terms))
    <div>
        <h2 class="capitalize text-2xl font-bold mb-4">{{ $allItemsLabel }}</h2>

        <x-carousel>
            <x-carousel.container class="pb-12">
                @foreach($terms as $term)
                    <x-carousel.item class="basis-1/6 mr-4">
                        @php
                            // Try to get the category thumbnail (featured image) for the term
                            $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
                            $thumbnail_url = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : null;
                        @endphp
                        <a href="{{ get_term_link($term) }}"
                            class="rounded-md bg-neutral-800 text-white shadow-xl aspect-square flex no-underline! relative overflow-hidden group">
                            <div @if($thumbnail_url)
                                class="absolute inset-0 group-hover:scale-105 transition-transform duration-300"
                                style="background-image: url('{{ $thumbnail_url }}'); background-size: cover; background-position: center;"
                            @endif></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                            <div class="p-4 relative flex-1 flex flex-col justify-end">
                                <h3 class="text-lg font-semibold">{!! $term->name !!}</h3>
                                @if(!empty($term->description))
                                    <p class="mt-1 text-sm text-neutral-300">{!! $term->description !!}</p>
                                @endif
                            </div>
                        </a>
                    </x-carousel.item>
                @endforeach
            </x-carousel.container>

            <x-carousel.prev class="btn btn-outline btn-icon right-10 left-auto! -top-8!" />
            <x-carousel.next class="btn btn-outline btn-icon right-0! left-auto! -top-8!" />
        </x-carousel>
    </div>
@endif

{{-- @push('afc_debug')
    {{ $context['_meta']['slug'] ?? '' }}
    {!! json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
@endpush --}}