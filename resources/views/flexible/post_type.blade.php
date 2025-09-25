@php
    // Read context
    $post_type = data_get($context, 'post_type.name', 'post');
    $title = data_get($context, 'title', '');
    $max_posts = (int) data_get($context, 'max_posts', 12);
    $order_by = data_get($context, 'order_by', 'date');
    $order = data_get($context, 'order', 'DESC');
    $post_filters = data_get($context, 'post_filters', []);
    $product_filters = data_get($context, 'product_filters', []);
    $product_taxonomies = data_get($product_filters, 'product_taxonomies', []);

    // Product filters
    $cat_terms = array_map('intval', data_get($product_filters, 'category_terms', []));
    $tag_terms = array_map('intval', data_get($product_filters, 'tag_terms', []));
    $occasion_terms = array_map('intval', data_get($product_filters, 'occasion_terms', data_get($product_filters, 'occasion_terms', [])));
    $season_terms = array_map('intval', data_get($product_filters, 'season_terms', data_get($product_filters, 'season_terms', [])));
    $audience_terms = array_map('intval', data_get($product_filters, 'audience_terms', data_get($product_filters, 'audience_terms', [])));

    $on_sale = (bool) data_get($product_filters, 'on_sale', false);
    $featured = (bool) data_get($product_filters, 'featured', false);
    $best_sellers = (bool) data_get($product_filters, 'best_sellers', false);
    $is_new = (bool) data_get($product_filters, 'is_new', false);

    // Safely extract the 'name' from arrays or objects
    $taxonomy_names = array_values(array_filter(array_map(function ($t) {
        if (is_array($t)) {
            return $t['name'] ?? null;
        }
        if (is_object($t)) {
            return $t->name ?? null;
        }
        return null;
    }, (array) $product_taxonomies)));

    // Base query
    $args = [
        'post_type' => $post_type,
        'posts_per_page' => $max_posts,
        'post_status' => 'publish',
        'orderby' => $order_by,
        'order' => $order,
    ];

    // Woo defaults (visibility, stock, etc.)
    if ($post_type === 'product' && function_exists('WC')) {
        $args['tax_query'] = WC()->query->get_tax_query();
        $args['meta_query'] = WC()->query->get_meta_query();
    }

    // On sale - Products (includes variation parents)
    if ($on_sale && $post_type === 'product' && function_exists('wc_get_product_ids_on_sale')) {
        $sale_ids = array_map('intval', wc_get_product_ids_on_sale());

        if (!empty($sale_ids)) {
            $variation_ids = get_posts([
                'post_type' => 'product_variation',
                'post__in' => $sale_ids,
                'fields' => 'ids',
                'posts_per_page' => -1,
                'no_found_rows' => true,
            ]);

            if (!empty($variation_ids)) {
                $parent_ids = array_map(static function ($vid) {
                    return (int) get_post_field('post_parent', $vid);
                }, $variation_ids);

                $sale_ids = array_values(array_unique(array_merge($sale_ids, $parent_ids)));
            }
        }

        // Constrain to on-sale only (or force empty if none)
        $args['post__in'] = !empty($sale_ids) ? array_slice($sale_ids, 0, $max_posts) : [0];

        // Keep the order of post__in (optional but recommended)
        $args['orderby'] = 'post__in';
    }

    // Featured - Products (intersect with existing post__in if present)
    if ($featured && $post_type === 'product' && function_exists('wc_get_featured_product_ids')) {
        $featured_ids = array_map('intval', wc_get_featured_product_ids());

        if (!empty($featured_ids)) {
            $args['post__in'] = !empty($args['post__in'])
                ? array_values(array_intersect($args['post__in'], $featured_ids))
                : array_slice($featured_ids, 0, $max_posts);

            // Preserve chosen order if post__in exists
            $args['orderby'] = 'post__in';
        } else {
            $args['post__in'] = [0];
        }
    }

    // Best Sellers - Products
    if ($best_sellers && $post_type === 'product') {
        $args['meta_key'] = 'total_sales';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
    }

    // Is New - Products (just ensure newest first)
    if ($is_new && $post_type === 'product') {
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
    }

    // Extra taxonomy filters (only if set)
    $extra_tax = [];
    if ($post_type === 'product') {
        if (!empty($cat_terms) && in_array('product_cat', $taxonomy_names, true)) {
            $extra_tax[] = ['taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => $cat_terms, 'operator' => 'IN'];
        }
        if (!empty($tag_terms) && in_array('product_tag', $taxonomy_names, true)) {
            $extra_tax[] = ['taxonomy' => 'product_tag', 'field' => 'term_id', 'terms' => $tag_terms, 'operator' => 'IN'];
        }
        if (!empty($occasion_terms) && in_array('product_occasion', $taxonomy_names, true)) {
            $extra_tax[] = ['taxonomy' => 'product_occasion', 'field' => 'term_id', 'terms' => $occasion_terms, 'operator' => 'IN'];
        }
        if (!empty($season_terms) && in_array('product_season', $taxonomy_names, true)) {
            $extra_tax[] = ['taxonomy' => 'product_season', 'field' => 'term_id', 'terms' => $season_terms, 'operator' => 'IN'];
        }
        if (!empty($audience_terms) && in_array('product_audience', $taxonomy_names, true)) {
            $extra_tax[] = ['taxonomy' => 'product_audience', 'field' => 'term_id', 'terms' => $audience_terms, 'operator' => 'IN'];
        }
        if ($featured) {
            $extra_tax[] = [
                'taxonomy' => 'product_visibility',
                'field' => 'name',
                'terms' => ['featured'],
                'operator' => 'IN',
                'include_children' => false,
            ];
        }
    }
    if (!empty($extra_tax)) {
        if (!isset($args['tax_query']) || !is_array($args['tax_query'])) {
            $args['tax_query'] = ['relation' => 'AND'];
        }
        foreach ($extra_tax as $clause) {
            $args['tax_query'][] = $clause;
        }
    }

    $q = new WP_Query($args);
@endphp

@if ($q->have_posts())
    <div>
        <h2 class="capitalize text-2xl font-bold mb-4">{!! e($title) !!}</h2>

        @if ($post_type === 'product' && function_exists('woocommerce_product_loop_start'))
            @php
                // Let Woo know we're in a product loop (some templates rely on this)
                wc_setup_loop(['columns' => 3]);
            @endphp

            <x-carousel>
                {{-- Keep Woo classes at the container level so theme CSS (e.g. .products .product) still applies --}}
                <x-carousel.container class="products pb-12">
                    @while ($q->have_posts()) @php $q->the_post(); @endphp
                        @php
                            // Capture Woo's standard card HTML (normally <li class="product">…</li>)
                            ob_start();
                            wc_get_template_part('content', 'product');
                            $card = trim(ob_get_clean());

                            // Strip the outer <li> wrapper so we can place the card inside our slide
                            // This keeps inner markup (thumbnail, price, badges, etc.) intact.
                            if (preg_match('/^<li\b[^>]*>([\s\S]*)<\/li>$/i', $card, $m)) {
                                $card_inner = $m[1];
                            } else {
                                // Fallback: if template changed and isn't <li>, just use it as-is
                                $card_inner = $card;
                            }
                        @endphp

                        <x-carousel.item class="basis-1/6 md:basis-1/4 lg:basis-1/6 mr-4">
                            {{-- Re-wrap as a div with the same .product class so theme CSS still hits it --}}
                            <div class="product">
                                {!! $card_inner !!}
                            </div>
                        </x-carousel.item>
                    @endwhile
                </x-carousel.container>

                <x-carousel.prev class="btn btn-outline btn-icon right-10 left-auto! -top-8!" />
                <x-carousel.next class="btn btn-outline btn-icon right-0! left-auto! -top-8!" />
            </x-carousel>

            @php wc_reset_loop(); @endphp
        @else
            {{-- Non-product fallback grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @while ($q->have_posts()) @php $q->the_post(); @endphp
                    <article class="border rounded-lg p-4">
                        <a class="block text-lg font-semibold mb-2" href="{{ get_permalink() }}">{{ get_the_title() }}</a>
                        @if (has_post_thumbnail())
                            <a href="{{ get_permalink() }}" class="block mb-3">
                                {!! get_the_post_thumbnail(get_the_ID(), 'medium', ['class' => 'w-full h-auto rounded']) !!}
                            </a>
                        @endif
                        <div class="prose prose-sm">{!! wp_kses_post(get_the_excerpt()) !!}</div>
                    </article>
                @endwhile
            </div>
        @endif

        @php wp_reset_postdata(); @endphp
    </div>
@else
    <p>No products on sale right now.</p>
@endif