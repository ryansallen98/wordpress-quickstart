<?php

namespace App\View\Composers\WooCommerce\Cart;

use Roots\Acorn\View\Composer;

class MiniCart extends Composer
{
    protected static $views = ['woocommerce.cart.mini-cart'];

    public function with(): array
    {
        $args = $this->view->getData()['args'] ?? [];

        return [
            'list_class' => $args['list_class'] ?? '',
            'items'      => $this->cartItems(),
            'cart_empty' => ! (WC()->cart && ! WC()->cart->is_empty()),
        ];
    }

    protected function cartItems(): array
    {
        $items = [];
        if (! WC()->cart) {
            return $items;
        }

        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
            $productId = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

            if (! $product || ! $product->exists() || (int) ($cart_item['quantity'] ?? 0) <= 0) {
                continue;
            }
            if (! apply_filters('woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key)) {
                continue;
            }

            // Parent/base title (no variation suffix)
            if ($product->is_type('variation')) {
                $parent    = wc_get_product($product->get_parent_id());
                $baseTitle = $parent ? $parent->get_name() : $product->get_name();
                $baseProd  = $parent ?: $product;
            } else {
                $baseTitle = $product->get_name();
                $baseProd  = $product;
            }

            // Woo fragments
            $thumbHtml    = apply_filters('woocommerce_cart_item_thumbnail', $product->get_image(), $cart_item, $cart_item_key);
            $priceHtml    = apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($product), $cart_item, $cart_item_key);
            $permalink    = apply_filters('woocommerce_cart_item_permalink', $product->is_visible() ? $product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
            $subtotalHtml = apply_filters(
                'woocommerce_cart_item_subtotal',
                WC()->cart->get_product_subtotal($product, (int) $cart_item['quantity']),
                $cart_item,
                $cart_item_key
            );

            // Build attributes list shown under the title
            $attributes = [];

            // Helpers
            $pushAttr = static function (array &$list, string $label, $value): void {
                $label = trim($label);
                if ($label === '' || $value === null) return;

                $toHtml = static function ($val): string {
                    if (is_bool($val)) {
                        return $val ? __('Yes', 'woocommerce') : __('No', 'woocommerce');
                    }
                    if (is_numeric($val)) {
                        // Try as attachment ID
                        $url = wp_get_attachment_url((int) $val);
                        return $url ? sprintf('<a href="%s" target="_blank" rel="noopener">%s</a>', esc_url($url), esc_html(basename($url))) : (string) $val;
                    }
                    if (is_array($val)) {
                        // Files/values arrays → collect meaningful parts
                        $parts = [];

                        // First pass: known file objects
                        foreach ($val as $entry) {
                            if (is_array($entry) && !empty($entry['url'])) {
                                $fname = !empty($entry['filename']) ? $entry['filename'] : basename((string) $entry['url']);
                                $parts[] = sprintf('<a href="%s" target="_blank" rel="noopener">%s</a>', esc_url($entry['url']), esc_html($fname));
                            }
                        }

                        // Fallback: flatten scalars/labels
                        if (!$parts) {
                            array_walk_recursive($val, function ($v) use (&$parts) {
                                if (is_scalar($v)) {
                                    $s = trim((string) $v);
                                    if ($s !== '') $parts[] = esc_html($s);
                                }
                            });
                        }

                        // Remove common junk tokens
                        $parts = array_values(array_filter($parts, static function ($t) {
                            $t = trim(wp_strip_all_tags((string) $t));
                            return $t !== '' && $t !== '0' && mb_strtolower($t) !== 'none';
                        }));

                        return implode(', ', $parts);
                    }
                    if (is_string($val)) {
                        $s = trim($val);
                        if ($s === '' || $s === '0' || mb_strtolower($s) === 'none') return '';
                        if (filter_var($s, FILTER_VALIDATE_URL)) {
                            $name = basename(parse_url($s, PHP_URL_PATH) ?: $s);
                            return sprintf('<a href="%s" target="_blank" rel="noopener">%s</a>', esc_url($s), esc_html($name));
                        }
                        return esc_html($s);
                    }
                    return '';
                };

                $html = $toHtml($value);
                if ($html === '') return;

                // De-dupe by label+value (label compared case-insensitively)
                foreach ($list as $row) {
                    if (mb_strtolower($row['key']) === mb_strtolower($label) && wp_kses_post($row['value']) === wp_kses_post($html)) {
                        return;
                    }
                }

                $list[] = ['key' => $label, 'value' => wp_kses_post($html)];
            };

            $prettyLabel = static function (string $key): string {
                $label = str_replace(['_', '-'], ' ', trim($key));
                $label = preg_replace('/\s+/', ' ', $label);
                return mb_convert_case($label, MB_CASE_TITLE, 'UTF-8');
            };

            // 1) Variation attributes (customer choices for variable products)
            if ($product->is_type('variation')) {
                $varAtts = $product->get_variation_attributes(); // e.g. ['attribute_pa_size' => 'large']
                if (is_array($varAtts)) {
                    foreach ($varAtts as $attrKey => $attrVal) {
                        $taxonomyOrName = str_replace('attribute_', '', (string) $attrKey); // 'pa_size' or custom
                        $label = wc_attribute_label($taxonomyOrName, $baseProd);
                        if (taxonomy_exists($taxonomyOrName)) {
                            $term  = get_term_by('slug', (string) $attrVal, $taxonomyOrName);
                            $value = $term ? $term->name : wc_clean(str_replace(['-', '_'], ' ', (string) $attrVal));
                        } else {
                            $value = wc_clean((string) $attrVal);
                        }
                        $pushAttr($attributes, $label, $value);
                    }
                }
            }

            // 2) Generic cart item data (most plugins hook here — APF, Official Add-Ons, etc.)
            $rows = wc_get_formatted_cart_item_data($cart_item, true);
            if (!is_array($rows)) {
                $rows = [];
            }
            foreach ($rows as $row) {
                if (!is_array($row) || !empty($row['hidden'])) continue;
                $label = isset($row['key']) ? (string) $row['key'] : '';
                if ($label === '') continue;
                $value = $row['display'] ?? '';
                $pushAttr($attributes, $label, $value);
            }

            // 3) Plugin-specific fallbacks (if they didn’t use wc_get_formatted_cart_item_data)

            // 3a) APF (Studio Wombat) — clean, meaningful values only
            $formatApfValue = static function (array $entry): string {
                // Prefer 'values' (array)
                if (!empty($entry['values']) && is_array($entry['values'])) {
                    $out = [];
                    foreach ($entry['values'] as $v) {
                        if (is_array($v) && !empty($v['url'])) {
                            $fname = !empty($v['filename']) ? $v['filename'] : basename((string) $v['url']);
                            $out[] = sprintf('<a href="%s" target="_blank" rel="noopener">%s</a>', esc_url($v['url']), esc_html($fname));
                        } elseif (is_array($v) && !empty($v['label'])) {
                            $out[] = esc_html((string) $v['label']);
                        } elseif (is_array($v) && !empty($v['value'])) {
                            $out[] = esc_html((string) $v['value']);
                        } elseif (is_scalar($v)) {
                            $out[] = esc_html((string) $v);
                        }
                    }
                    // Filter out noise
                    $out = array_values(array_filter($out, static function ($t) {
                        $t = trim(wp_strip_all_tags((string) $t));
                        return $t !== '' && $t !== '0' && mb_strtolower($t) !== 'none';
                    }));
                    return implode(', ', $out);
                }

                // Fallback to 'value'
                if (array_key_exists('value', $entry)) {
                    $val = $entry['value'];
                    if (is_array($val)) {
                        $parts = [];
                        foreach ($val as $v) {
                            if (is_array($v) && !empty($v['url'])) {
                                $fname = !empty($v['filename']) ? $v['filename'] : basename((string) $v['url']);
                                $parts[] = sprintf('<a href="%s" target="_blank" rel="noopener">%s</a>', esc_url($v['url']), esc_html($fname));
                            } elseif (is_array($v) && !empty($v['label'])) {
                                $parts[] = esc_html((string) $v['label']);
                            } elseif (is_scalar($v)) {
                                $parts[] = esc_html((string) $v);
                            }
                        }
                        $parts = array_values(array_filter($parts, static function ($t) {
                            $t = trim(wp_strip_all_tags((string) $t));
                            return $t !== '' && $t !== '0' && mb_strtolower($t) !== 'none';
                        }));
                        return implode(', ', $parts);
                    }
                    $s = trim((string) $val);
                    if ($s === '' || $s === '0' || mb_strtolower($s) === 'none') return '';
                    if (filter_var($s, FILTER_VALIDATE_URL)) {
                        $name = basename(parse_url($s, PHP_URL_PATH) ?: $s);
                        return sprintf('<a href="%s" target="_blank" rel="noopener">%s</a>', esc_url($s), esc_html($name));
                    }
                    return esc_html($s);
                }

                return '';
            };

            foreach (['wapf', 'wapf_fields', 'advanced_product_fields'] as $apfKey) {
                if (!empty($cart_item[$apfKey]) && is_array($cart_item[$apfKey])) {
                    foreach ($cart_item[$apfKey] as $entry) {
                        if (!is_array($entry)) continue;
                        $label = (string) ($entry['label'] ?? ($entry['name'] ?? ''));
                        if ($label === '') continue;
                        $valueHtml = $formatApfValue($entry);
                        if ($valueHtml !== '') {
                            $pushAttr($attributes, $label, $valueHtml);
                        }
                    }
                }
            }

            // 3b) WooCommerce Product Add-Ons (official) — raw fallback
            if (!empty($cart_item['addons']) && is_array($cart_item['addons'])) {
                foreach ($cart_item['addons'] as $addon) {
                    if (!is_array($addon)) continue;
                    $label = (string) ($addon['name'] ?? '');
                    if ($label === '') continue;
                    $value = $addon['value'] ?? '';
                    if (is_array($value)) $value = implode(', ', array_map('strval', $value));
                    $pushAttr($attributes, $label, $value);
                }
            }

            // 3c) TM Extra Product Options
            if (!empty($cart_item['tmcartepo']) && is_array($cart_item['tmcartepo'])) {
                foreach ($cart_item['tmcartepo'] as $opt) {
                    if (!is_array($opt)) continue;
                    $label = (string) ($opt['name'] ?? '');
                    if ($label === '') continue;
                    $value = $opt['value_display'] ?? ($opt['value'] ?? '');
                    $pushAttr($attributes, $label, $value);
                }
            }

            // 4) Optional whitelisted custom cart item meta
            $whitelist = apply_filters('mini_cart_custom_item_meta_whitelist', [
                'delivery_date', 'delivery_time', 'message', 'ribbon_color', 'uploaded_image',
                'acf_delivery_date', 'acf_delivery_time',
            ]);
            if (is_array($whitelist)) {
                foreach ($whitelist as $key) {
                    if (isset($cart_item[$key])) {
                        $pushAttr($attributes, $prettyLabel((string) $key), $cart_item[$key]);
                    }
                }
            }

            // Build the item payload
            $items[] = [
                'key'            => $cart_item_key,
                'class'          => apply_filters('woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key),

                'permalink'      => $permalink,
                'display_title'  => wp_kses_post($baseTitle),
                'thumb_html'     => $thumbHtml,
                'price_html'     => $priceHtml,
                'subtotal_html'  => $subtotalHtml,
                'attributes'     => $attributes,
                'qty'            => (int) $cart_item['quantity'],

                'remove' => [
                    'url'             => esc_url(wc_get_cart_remove_url($cart_item_key)),
                    'aria_label'      => esc_attr(sprintf(__('Remove %s from cart', 'woocommerce'), wp_strip_all_tags($baseTitle))),
                    'product_id'      => esc_attr($productId),
                    'cart_item_key'   => esc_attr($cart_item_key),
                    'sku'             => esc_attr($product->get_sku()),
                    'success_message' => esc_attr(sprintf(__('&ldquo;%s&rdquo; has been removed from your cart', 'woocommerce'), wp_strip_all_tags($baseTitle))),
                ],
            ];
        }

        return $items;
    }
}