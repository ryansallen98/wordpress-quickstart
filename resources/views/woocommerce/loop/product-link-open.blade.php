<?php
defined('ABSPATH') || exit();

/** @var WC_Product|null $product */
global $product;
if (! $product instanceof WC_Product) {
  return;
}

$permalink = get_permalink($product->get_id());
if (! $permalink) {
  return;
}

// Customize classes/attrs freely
$classes = implode(' ', [
  'wc-loop-link',
  'group',
  'block',
  'rounded-md',
  'mb-4',
  'group/product-link',
  'no-underline!',
  'w-full'
]);

$title_attr = the_title_attribute(['echo' => false]);
?>

<a
  href="<?php echo esc_url($permalink); ?>"
  class="<?php echo esc_attr($classes); ?>"
  aria-label="<?php echo esc_attr($title_attr); ?>"
>
