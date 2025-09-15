{{-- resources/views/woocommerce/single-product-reviews.blade.php --}}
@php
if (!defined('ABSPATH')) { exit; }

global $product;

if (!comments_open()) {
    return;
}
@endphp

<div id="reviews" class="woocommerce-Reviews">
    <div id="comments" class="mb-8">
        <h2 class="woocommerce-Reviews-title text-md font-bold mb-4">
            @php
                $count = $product->get_review_count();
                if ($count && wc_review_ratings_enabled()) {
                    /** translators: 1: reviews count 2: product name */
                    $reviews_title = sprintf(
                        esc_html(_n('%1$s review for %2$s', '%1$s reviews for %2$s', $count, 'woocommerce')),
                        esc_html($count),
                        '<span>' . get_the_title() . '</span>'
                    );
                    echo apply_filters('woocommerce_reviews_title', $reviews_title, $count, $product); // phpcs:ignore WordPress.Security.EscapeOutput
                } else {
                    esc_html_e('Reviews', 'woocommerce');
                }
            @endphp
        </h2>

        @if (have_comments())
            <ol class="commentlist">
                @php
                    wp_list_comments(apply_filters('woocommerce_product_review_list_args', ['callback' => 'woocommerce_comments']));
                @endphp
            </ol>

            @php
                if (get_comment_pages_count() > 1 && get_option('page_comments')) :
                    echo '<nav class="woocommerce-pagination">';
                    paginate_comments_links(apply_filters('woocommerce_comment_pagination_args', [
                        'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
                        'next_text' => is_rtl() ? '&larr;' : '&rarr;',
                        'type'      => 'list',
                    ]));
                    echo '</nav>';
                endif;
            @endphp
        @else
            <p class="woocommerce-noreviews">@php esc_html_e('There are no reviews yet.', 'woocommerce'); @endphp</p>
        @endif
    </div>

    @if ( get_option('woocommerce_review_rating_verification_required') === 'no' || wc_customer_bought_product('', get_current_user_id(), $product->get_id()) )
        <div id="review_form_wrapper">
            <div id="review_form">
                @php
                    $commenter    = wp_get_current_commenter();
                    $comment_form = [
                        /** translators: %s is product title */
                        'title_reply'         => have_comments()
                            ? esc_html__('Add a review', 'woocommerce')
                            : sprintf(esc_html__('Be the first to review &ldquo;%s&rdquo;', 'woocommerce'), get_the_title()),
                        /** translators: %s is product title */
                        'title_reply_to'      => esc_html__('Leave a Reply to %s', 'woocommerce'),

                        // Title wrapper with classes
                        'title_reply_before'  => '<h3 id="reply-title" class="comment-reply-title text-md font-bold mb-3" role="heading" aria-level="3">',
                        'title_reply_after'   => '</h3>',

                        'comment_notes_after' => '',
                        'label_submit'        => esc_html__('Submit', 'woocommerce'),

                        // Submit button classes
                        'class_submit'        => 'btn btn-outline btn-sm mt-2 h-auto',

                        'logged_in_as'        => '',
                        'comment_field'       => '',
                    ];

                    $name_email_required = (bool) get_option('require_name_email', 1);
                    $fields = [
                        'author' => [
                            'label'        => __('Name', 'woocommerce'),
                            'type'         => 'text',
                            'value'        => $commenter['comment_author'],
                            'required'     => $name_email_required,
                            'autocomplete' => 'name',
                        ],
                        'email'  => [
                            'label'        => __('Email', 'woocommerce'),
                            'type'         => 'email',
                            'value'        => $commenter['comment_author_email'],
                            'required'     => $name_email_required,
                            'autocomplete' => 'email',
                        ],
                    ];

                    $comment_form['fields'] = [];

                    foreach ($fields as $key => $field) {
                        $field_html  = '<p class="comment-form-' . esc_attr($key) . '">';
                        $field_html .= '<label class="input-label" for="' . esc_attr($key) . '">' . esc_html($field['label']);

                        if ($field['required']) {
                            $field_html .= '&nbsp;<span class="required">*</span>';
                        }

                        $field_html .= '</label><input class="input-text" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" type="' . esc_attr($field['type']) . '" autocomplete="' . esc_attr($field['autocomplete']) . '" value="' . esc_attr($field['value']) . '" size="30" ' . ($field['required'] ? 'required' : '') . ' /></p>';

                        $comment_form['fields'][$key] = $field_html;
                    }

                    $account_page_url = wc_get_page_permalink('myaccount');
                    if ($account_page_url) {
                        /** translators: %s opening and closing link tags respectively */
                        $comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf(esc_html__('You must be %1$slogged in%2$s to post a review.', 'woocommerce'), '<a href="' . esc_url($account_page_url) . '">', '</a>') . '</p>';
                    }
                @endphp

                {{-- ⭐ Custom star rating (outlined/filled heroicons) captured via output buffering so Blade can render --}}
                @php
                    if (wc_review_ratings_enabled()) {
                        ob_start();
                @endphp
                    <fieldset class="comment-form-rating" aria-labelledby="comment-form-rating-label"
                             x-data="{ hover: 0, value: Number(document.querySelector('input[name=rating]:checked')?.value || 0) }">
                        <legend id="comment-form-rating-label" class="input-label mb-2">
                            {{ esc_html__('Your rating', 'woocommerce') }}
                            @if (wc_review_ratings_required())
                                <span class="required">*</span>
                            @endif
                        </legend>

                        <div class="flex items-center">
                            @for ($v = 1; $v <= 5; $v++)
                                <div class="relative">
                                    <input
                                        type="radio"
                                        id="rating-{{ $v }}"
                                        name="rating"
                                        value="{{ $v }}"
                                        class="sr-only"
                                        @if (wc_review_ratings_required()) required @endif
                                        x-model="value"
                                    />
                                    <label
                                        for="rating-{{ $v }}"
                                        class="cursor-pointer block"
                                        @mouseenter="hover = {{ $v }}"
                                        @mouseleave="hover = 0"
                                    >
                                        <span class="relative inline-flex items-center justify-center">
                                            {{-- outline (empty) --}}
                                            <x-dynamic-component
                                                :component="'heroicon-o-star'"
                                                class="h-6 w-6 text-muted-foreground/50"
                                                aria-hidden="true"
                                            />
                                            {{-- solid (filled) --}}
                                            <template x-if="(hover || value) >= {{ $v }}">
                                                <x-dynamic-component
                                                    :component="'heroicon-s-star'"
                                                    class="h-6 w-6 text-amber-500 absolute inset-0"
                                                    aria-hidden="true"
                                                />
                                            </template>
                                            <span class="sr-only">{{ sprintf(__('%s star', 'woocommerce'), $v) }}</span>
                                        </span>
                                    </label>
                                </div>
                            @endfor
                        </div>

                        <noscript>
                            <div class="mt-3">
                                <label class="input-label mb-2" for="rating-select">{{ esc_html__('Your rating', 'woocommerce') }}</label>
                                <select id="rating-select" name="rating" @if (wc_review_ratings_required()) required @endif>
                                    <option value="">{{ esc_html__('Rate&hellip;', 'woocommerce') }}</option>
                                    <option value="5">{{ esc_html__('Perfect', 'woocommerce') }}</option>
                                    <option value="4">{{ esc_html__('Good', 'woocommerce') }}</option>
                                    <option value="3">{{ esc_html__('Average', 'woocommerce') }}</option>
                                    <option value="2">{{ esc_html__('Not that bad', 'woocommerce') }}</option>
                                    <option value="1">{{ esc_html__('Very poor', 'woocommerce') }}</option>
                                </select>
                            </div>
                        </noscript>
                    </fieldset>
                @php
                        $comment_form['comment_field'] = ob_get_clean();
                    }
                @endphp

                @php
                    // Review textarea after the stars
                    $comment_form['comment_field'] .= '<p class="comment-form-comment flex flex-col"><label class="input-label mb-2" for="comment">' . esc_html__('Your review', 'woocommerce') . '&nbsp;<span class="required">*</span></label><textarea class="input-text" id="comment" name="comment" cols="45" rows="8" required></textarea></p>';

                    // Finally render the form
                    comment_form(apply_filters('woocommerce_product_review_comment_form_args', $comment_form));
                @endphp
            </div>
        </div>
    @else
        <p class="woocommerce-verification-required text-muted-foreground text-sm">@php esc_html_e('Only logged in customers who have purchased this product may leave a review.', 'woocommerce'); @endphp</p>
    @endif

    <div class="clear"></div>
</div>