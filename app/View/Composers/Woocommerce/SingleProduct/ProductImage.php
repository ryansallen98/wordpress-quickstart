<?php

namespace App\View\Composers\WooCommerce\SingleProduct;

use Roots\Acorn\View\Composer;
use Automattic\WooCommerce\Enums\ProductType;

class ProductImage extends Composer
{
    protected static $views = [
        'woocommerce.single-product.product-image',
    ];

    public function with(): array
    {
        if (! function_exists('wc_get_gallery_image_html')) {
            return ['can_render' => false];
        }

        global $product;

        $columns           = (int) apply_filters('woocommerce_product_thumbnails_columns', 4);
        $post_thumbnail_id = $product ? (int) $product->get_image_id() : 0;
        $gallery_ids       = $product ? (array) $product->get_gallery_image_ids() : [];

        $wrapper_classes = apply_filters('woocommerce_single_product_image_gallery_classes', [
            'woocommerce-product-gallery',
            'woocommerce-product-gallery--' . ($post_thumbnail_id ? 'with-images' : 'without-images'),
            'woocommerce-product-gallery--columns-' . absint($columns),
            'images',
        ]);

        $wrapper_class_attr = esc_attr(implode(' ', array_map('sanitize_html_class', (array) $wrapper_classes)));

        // Primary/full-size attrs
        $primary = $post_thumbnail_id ? $this->buildImageAttributes($post_thumbnail_id) : null;

        // Placeholder if no primary
        $placeholder = null;
        if (! $primary) {
            $is_variable_with_images = $product
                && $product->is_type(ProductType::VARIABLE)
                && ! empty($product->get_available_variations('image'));

            $wrapper_classname = $is_variable_with_images
                ? 'woocommerce-product-gallery__image woocommerce-product-gallery__image--placeholder'
                : 'woocommerce-product-gallery__image--placeholder';

            $placeholder = [
                'wrapper_class' => $wrapper_classname,
                'src'           => esc_url(wc_placeholder_img_src('woocommerce_single')),
                'alt'           => esc_html__('Awaiting product image', 'woocommerce'),
                'class'         => 'wp-post-image',
            ];
        }

        // Gallery (full-size attrs)
        $gallery = [];
        foreach ($gallery_ids as $gid) {
            $attrs = $this->buildImageAttributes((int) $gid);
            if ($attrs) {
                $gallery[] = $attrs;
            }
        }

        // Build THUMBNAILS (small images) in slide order: primary → gallery
        $thumbnails = [];

        if ($primary) {
            $thumbnails[] = $this->buildThumb($primary['id'], $primary['alt'] ?? '');
        } else {
            // Small placeholder thumb
            $phSmall = wc_placeholder_img_src('woocommerce_thumbnail');
            $thumbnails[] = [
                'src' => esc_url($phSmall),
                'alt' => $placeholder['alt'] ?? '',
            ];
        }

        foreach ($gallery_ids as $gid) {
            $thumb = $this->buildThumb((int) $gid);
            if ($thumb) {
                $thumbnails[] = $thumb;
            }
        }

        $is_variable_with_images = $product
            && $product->is_type(ProductType::VARIABLE)
            && ! empty($product->get_available_variations('image'));

        return [
            'can_render'              => true,
            'columns'                 => $columns,
            'wrapper_classes'         => $wrapper_classes,
            'wrapper_class_attr'      => $wrapper_class_attr,
            'has_images'              => (bool) $post_thumbnail_id,
            'is_variable_with_images' => (bool) $is_variable_with_images,
            'primary'                 => $primary,     // null if no image
            'gallery'                 => $gallery,     // [] if none
            'placeholder'             => $placeholder, // null if not needed
            'thumbnails'              => $thumbnails,  // 👈 small images for the thumb rail
        ];
    }

    /**
     * Full-size/primary attributes for the main carousel.
     */
    protected function buildImageAttributes(int $attachment_id): ?array
    {
        $main = wp_get_attachment_image_src($attachment_id, 'woocommerce_single');
        if (! $main) {
            return null;
        }

        $full   = wp_get_attachment_image_src($attachment_id, 'full');
        $srcset = wp_get_attachment_image_srcset($attachment_id, 'woocommerce_single') ?: '';
        $sizes  = wp_get_attachment_image_sizes($attachment_id, 'woocommerce_single') ?: '';

        $alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
        if ($alt === '') {
            $alt = get_the_title($attachment_id) ?: '';
        }

        $data = [
            'large_image'        => $full ? $full[0] : $main[0],
            'large_image_width'  => $full ? (int) $full[1] : (int) $main[1],
            'large_image_height' => $full ? (int) $full[2] : (int) $main[2],
        ];

        return [
            'id'       => $attachment_id,
            'alt'      => esc_attr($alt),
            'src'      => esc_url($main[0]),
            'width'    => (int) $main[1],
            'height'   => (int) $main[2],
            'srcset'   => $srcset,
            'sizes'    => $sizes,
            'full'     => [
                'src'    => $full ? esc_url($full[0]) : esc_url($main[0]),
                'width'  => $full ? (int) $full[1] : (int) $main[1],
                'height' => $full ? (int) $full[2] : (int) $main[2],
            ],
            'data'     => $data,
            'title'    => get_the_title($attachment_id) ?: '',
            'caption'  => wp_get_attachment_caption($attachment_id) ?: '',
            'mime'     => get_post_mime_type($attachment_id) ?: '',
        ];
    }

    /**
     * Small thumbnail (for the thumb rail).
     * Uses Woo sizes with safe fallbacks.
     */
    protected function buildThumb(int $attachment_id, string $altFallback = ''): ?array
    {
        // Prefer Woo thumbnail size, fall back to WP 'thumbnail'
        $thumb = wp_get_attachment_image_src($attachment_id, 'woocommerce_thumbnail')
              ?: wp_get_attachment_image_src($attachment_id, 'thumbnail');

        if (! $thumb) {
            return null;
        }

        $alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
        if ($alt === '') {
            $alt = $altFallback !== '' ? $altFallback : (get_the_title($attachment_id) ?: '');
        }

        return [
            'src'    => esc_url($thumb[0]),
            'alt'    => esc_attr($alt),
            'width'  => (int) $thumb[1],
            'height' => (int) $thumb[2],
        ];
    }
}