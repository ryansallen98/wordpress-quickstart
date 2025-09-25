<?php

namespace App\WooCommerce;

/**
 * Add search placeholder to Select2 fields
 */
add_action('wp_enqueue_scripts', function () {
  if (is_checkout()) {
    wp_add_inline_script(
      'selectWoo',
      <<<'JS'
jQuery(function($){
  // When any Select2 opens, set its search input placeholder
  $(document).on('select2:open', function(e){
    var target = e.target; // the <select>
    var ph = $(target).data('search-placeholder') || 'Search…';
    $('.select2-container--open .select2-search__field').attr('placeholder', ph);
  });
});
JS
      ,
      'after'
    );
  }
}, 100);

/**
 * Add custom svg arrow to Select2 fields
 */
add_action('wp_enqueue_scripts', function () {
    // Render the Lucide Blade icon into a string
    // If you use blade-ui-kit/blade-icons, the svg() helper is available:
    $icon = svg('lucide-chevron-down')->toHtml();

    // Escape newlines/quotes for safe JS embedding
    $icon_js = json_encode($icon);

    wp_add_inline_script('selectWoo', <<<JS
jQuery(function($){
  function replaceArrows(context){
    $('.select2-selection__arrow', context).each(function(){
      var \$arrow = $(this);
      if (\$arrow.data('lucide-arrow')) return; // prevent duplicates
      \$arrow.data('lucide-arrow', true);
      \$arrow.empty().append($icon_js);
    });
  }

  replaceArrows(document);
  $(document.body).on('country_to_state_changed updated_checkout', function(){
    replaceArrows(document);
  });
});
JS, 'after');
  
}, 100);



/**
 * Add 'wc-enhanced-select' class to variation attribute dropdowns
 */
add_filter('woocommerce_dropdown_variation_attribute_options_args', function ($args) {
    $args['class'] = trim(($args['class'] ?? '') . ' wc-enhanced-select2');
    return $args;
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('selectWoo'); // WooCommerce’s bundled Select2
    wp_enqueue_style('select2');    // Select2 CSS (WooCommerce includes this handle)
}, 20);