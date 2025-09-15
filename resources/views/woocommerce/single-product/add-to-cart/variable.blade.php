<?php
/**
 * Variable product add to cart
 *
 * Copy to: yourtheme/woocommerce/single-product/add-to-cart/variable.php
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.6.0
 */

defined('ABSPATH') || exit;

global $product;

$attribute_keys  = array_keys($attributes);
$variations_json = wp_json_encode($available_variations);
$variations_attr = function_exists('wc_esc_json') ? wc_esc_json($variations_json) : _wp_specialchars($variations_json, ENT_QUOTES, 'UTF-8', true);

do_action('woocommerce_before_add_to_cart_form'); ?>

<form class="variations_form cart mt-4 mb-8"
    action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>"
    method="post"
    enctype="multipart/form-data"
    data-product_id="<?php echo absint($product->get_id()); ?>"
    data-product_variations="<?php echo $variations_attr; // WPCS: XSS ok. ?>"
>
    <?php do_action('woocommerce_before_variations_form'); ?>

    <?php if (empty($available_variations) && false !== $available_variations): ?>
        <p class="stock out-of-stock">
            <?php echo esc_html(apply_filters('woocommerce_out_of_stock_message', __('This product is currently out of stock and unavailable.', 'woocommerce'))); ?>
        </p>
    <?php else: ?>

        <div class="variations">
            @foreach ($attributes as $attribute_name => $options)
                <div class="flex flex-col gap-2 justify-start items-start mb-2">
                    <div class="flex flex-col gap-2 w-full wc-enhanced-select2-wrapper">
                        <label class="input-label"
                            for="{!! esc_attr(sanitize_title($attribute_name)) !!}">
                            {!! wc_attribute_label($attribute_name) !!}
                        </label>

                        @php
                            wc_dropdown_variation_attribute_options([
                                'options'          => $options,
                                'attribute'        => $attribute_name,
                                'product'          => $product,
                                'class'            => 'input-select wc-enhanced-select2',
                                'show_option_none' => __('Choose an option', 'woocommerce'),
                            ]);
                        @endphp
                    </div>
                </div>
            @endforeach
        </div>

        <div class="reset_variations_alert screen-reader-text" role="alert" aria-live="polite" aria-relevant="all"></div>

        <?php do_action('woocommerce_after_variations_table'); ?>

        <div class="single_variation_wrap">
            <?php
            do_action('woocommerce_before_single_variation');
            do_action('woocommerce_single_variation');
            do_action('woocommerce_after_single_variation');
            ?>
        </div>

    <?php endif; ?>

    <?php wc_get_template('single-product/add-to-cart/variation.php', ['product' => $product]); ?>

    <?php do_action('woocommerce_after_variations_form'); ?>
</form>

<?php do_action('woocommerce_after_add_to_cart_form'); ?>

@pushOnce('styles')
<style>
/* Optional: keep SelectWoo control height consistent (adjust 42px if your inputs differ) */
.select2-container .select2-selection--single { height: 42px; }
.select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 42px; }
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 42px; }

/* Make sure dropdown floats above modals/drawers; bump if your stack needs more */
.select2-container.select2-container--open { z-index: 9999; }

/* If you see page shift when opening, this prevents scrollbar jump on some themes */
html.select2-open { overflow: visible !important; }
</style>
@endPushOnce

@pushOnce('scripts')
<script>
jQuery(function ($) {
  var mq = window.matchMedia('(max-width: 640px)'); // Tailwind sm

  function destroyEnhancement($select) {
    if (!$select || !$select.length) return;
    if ($select.data('select2')) {
      try {
        $select.selectWoo('destroy');
      } catch (e1) {
        try { $select.select2('destroy'); } catch (e2) {}
      }
    }
  }

  function enhance($select) {
    // Always start clean
    destroyEnhancement($select);

    // Mobile: keep native <select>
    if (mq.matches) return;

    // Desktop/tablet: enhance (support SelectWoo or Select2)
    var opts = {
      width: '100%',
      minimumResultsForSearch: 10,
      dropdownAutoWidth: true,
      dropdownParent: $(document.body)
    };

    if ($.fn.selectWoo) {
      $select.selectWoo(opts);
    } else if ($.fn.select2) {
      $select.select2(opts);
    }
  }

  function initVariationSelects(ctx) {
    $(ctx)
      .find('form.variations_form select[name^="attribute_"].wc-enhanced-select2')
      .each(function () { enhance($(this)); });
  }

  // Initial
  initVariationSelects(document);

  // Re-init when Woo updates variation values or resets data
  $(document).on('woocommerce_update_variation_values found_variation reset_data', function (e) {
    initVariationSelects($(e.target));
  });

  // Ensure Woo sees value changes from SelectWoo
  $(document).on('select2:select select2:clear', 'form.variations_form select[name^="attribute_"]', function () {
    $(this).trigger('change');
  });

  // Re-init after full load (modals/drawers, etc.)
  $(window).on('load', function () { initVariationSelects(document); });

  // Re-init on breakpoint change (rotate/resize)
  var onMQChange = function () { initVariationSelects(document); };
  if (mq.addEventListener) mq.addEventListener('change', onMQChange);
  else if (mq.addListener) mq.addListener(onMQChange); // older browsers

  // Prefer opening below + recalc + scroll into view (desktop/tablet only)
  $(document).on('select2:open', function (e) {
    if (mq.matches) return; // native on mobile
    var $sel = $(e.target);
    var s2 = $sel.data('select2');
    if (!s2) return;

    // Force below classes
    if (s2.$container && s2.$dropdown) {
      s2.$container.removeClass('select2-container--above').addClass('select2-container--below');
      s2.$dropdown.removeClass('select2-dropdown--above').addClass('select2-dropdown--below');
    } else {
      $('.select2-container--open').removeClass('select2-container--above').addClass('select2-container--below');
      $('.select2-container--open .select2-dropdown').removeClass('select2-dropdown--above').addClass('select2-dropdown--below');
    }

    // Reposition if available
    if (typeof s2.positionDropdown === 'function') {
      try { s2.positionDropdown(); } catch (e1) {}
    }

    // Make space below if needed
    var rect = $sel[0].getBoundingClientRect();
    var desired = 300; // px
    var below = window.innerHeight - rect.bottom;
    if (below < desired) {
      var top = window.scrollY + rect.top - (window.innerHeight / 3);
      window.scrollTo({ top: Math.max(top, 0), behavior: 'smooth' });
    }
  });

  // Cosmetic: class on <html> while open
  $(document).on('select2:open', function () { document.documentElement.classList.add('select2-open'); });
  $(document).on('select2:close', function () { document.documentElement.classList.remove('select2-open'); });
});
</script>
@endPushOnce