@if (!defined('ABSPATH'))
    @php exit; @endphp
@endif

<li {{ comment_class('', null, null, false) }} id="li-comment-{{ comment_ID() }}">

	<div id="comment-{{ comment_ID() }}" class="comment_container grid grid-cols-[32px_auto] gap-2 [&_.avatar]:size-8 [&_.avatar]:rounded-full [&_.avatar]:shadow-sm my-2 [&_.star-rating]:text-xs [&_.star-rating]:text-muted-foreground [&_.meta]:mb-1">

		@php
		/**
		 * The woocommerce_review_before hook
		 *
		 * @hooked woocommerce_review_display_gravatar - 10
		 */
		do_action('woocommerce_review_before', $comment);
		@endphp

		<div class="comment-text">

			@php
			/**
			 * The woocommerce_review_before_comment_meta hook.
			 *
			 * @hooked woocommerce_review_display_rating - 10
			 */
			do_action('woocommerce_review_before_comment_meta', $comment);

			/**
			 * The woocommerce_review_meta hook.
			 *
			 * @hooked woocommerce_review_display_meta - 10
			 */
			do_action('woocommerce_review_meta', $comment);

			do_action('woocommerce_review_before_comment_text', $comment);

			/**
			 * The woocommerce_review_comment_text hook
			 *
			 * @hooked woocommerce_review_display_comment_text - 10
			 */
			do_action('woocommerce_review_comment_text', $comment);

			do_action('woocommerce_review_after_comment_text', $comment);
			@endphp

		</div>
	</div>
