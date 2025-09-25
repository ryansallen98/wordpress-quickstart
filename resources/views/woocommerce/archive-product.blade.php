{{--
The Template for displaying product archives, including the main shop page which is a post type archive

This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.

HOWEVER, on occasion WooCommerce will need to update template files and you
(the theme developer) will need to copy the new files to your theme to
maintain compatibility. We try to do this as little as possible, but it does
happen. When this occurs the version of the template file will be bumped and
the readme will list any important changes.

@see https://docs.woocommerce.com/document/template-structure/
@package WooCommerce/Templates
@version 3.4.0
--}}

@extends('layouts.app')

@section('content')
  @php
    do_action('get_header', 'shop');
    do_action('woocommerce_before_main_content');
  @endphp

  @php
    // Get current queried object (category or shop page)
    $qo = get_queried_object();
    $bg = null;

    // Category archive background
    if ($qo instanceof WP_Term && $qo->taxonomy === 'product_cat') {
      $bg = get_field('background', 'product_cat_' . $qo->term_id);
    }

    // Shop page background
    if (!$bg && function_exists('is_shop') && is_shop()) {
      $shop_page_id = get_option('woocommerce_shop_page_id');
      $bg = $shop_page_id ? get_field('background', $shop_page_id) : null;
    }

    // Normalize background into a URL
    if (is_array($bg) && !empty($bg['url'])) {
      $bg_url = esc_url($bg['url']);
    } elseif (is_numeric($bg)) {
      $bg_url = esc_url(wp_get_attachment_url($bg));
    } elseif (is_string($bg)) {
      $bg_url = esc_url($bg);
    } else {
      $bg_url = null;
    }
  @endphp

  <header class="woocommerce-products-header bg-primary @if (!$bg_url) text-primary-foreground @else text-foreground dark:text-background @endif p-8 pt-24 rounded-lg shadow-lg my-4" @if ($bg_url) style="background-image: url('{{ $bg_url }}'); background-size: cover; background-position: center;" @endif>
    @if (apply_filters('woocommerce_show_page_title', true))
      <h1 class="text-3xl font-bold mb-1">{!! woocommerce_page_title(false) !!}</h1>
      <p class="text-lg">Browse our delicious selection of sweets</p>
    @endif

    @php
      do_action('woocommerce_archive_description')
    @endphp
  </header>

  @if (woocommerce_product_loop())
    @php
      do_action('woocommerce_before_shop_loop');
      woocommerce_product_loop_start();
    @endphp

    @if (wc_get_loop_prop('total'))
      @while (have_posts())
        @php
          the_post();
          do_action('woocommerce_shop_loop');
          wc_get_template_part('content', 'product');
        @endphp
      @endwhile
    @endif

    @php
      woocommerce_product_loop_end();
      do_action('woocommerce_after_shop_loop');
    @endphp
  @else
    @php
      do_action('woocommerce_no_products_found')
    @endphp
  @endif

  @php
    do_action('woocommerce_after_main_content');
    do_action('get_sidebar', 'shop');
    do_action('get_footer', 'shop');
  @endphp
@endsection