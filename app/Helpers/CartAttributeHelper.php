<?php

namespace App\Helpers;

/**
 * Utilities for normalizing WooCommerce cart & order item attributes.
 * Ensures user-entered meta (APF, addons, custom fields) is output safely,
 * with proper <a href> links for uploads/URLs.
 */
class CartAttributeHelper
{
    /**
     * Keys we should ignore when sweeping raw meta.
     */
    protected static array $ignoreKeys = [
        // existing…
        'line_subtotal',
        'line_subtotal_tax',
        'line_total',
        'line_tax',
        'qty',
        'quantity',
        'total',
        'tax',
        // add plugin internals
        'addons',
        'yith_wapo',
        'ywapo',
        'wapo',
        'wapf',
        'wapf_key',
        'wapf_field_groups',
        'wapf_field_group',
        'wapf_fields',
        'wapf_pricing',
    ];

    // Add/merge at the top of the class if not present:
    protected static array $nonUserValueKeys = [
        'price',
        'price_raw',
        'price_type',
        'price_type_raw',
        'currency',
        'qty',
        'quantity',
    ];

    /**
     * Strip tags/whitespace and remove trailing colon.
     */
    public static function cleanLabel($label): string
    {
        $label = is_string($label) ? $label : '';
        $label = trim(wp_strip_all_tags($label));
        return rtrim($label, ':');
    }

    /**
     * Derive a human label from a key like `delivery_date` or `deliveryDate`.
     */
    public static function labelFromKey(string $key): string
    {
        // camelCase → "camel Case"
        $spaced = preg_replace('/([a-z])([A-Z])/', '$1 $2', $key);
        // snake_case / kebab-case → spaces
        $spaced = str_replace(['_', '-'], ' ', $spaced);
        return static::cleanLabel(ucwords(trim($spaced)));
    }

    /**
     * Should we skip this key as reserved/technical?
     */
    public static function isReservedKey(string $key): bool
    {
        if ($key === '' || $key[0] === '_')
            return true;
        $lk = strtolower($key);
        if (in_array($lk, static::$ignoreKeys, true))
            return true;
        // ignore anything starting with wapf… or apf…
        if (preg_match('/^(wapf|apf)(_|$)/i', $key))
            return true;
        return false;
    }

    /**
     * Try to decode JSON if the input looks like JSON.
     */
    public static function maybeJsonDecode($raw)
    {
        if (is_string($raw)) {
            $t = ltrim($raw);
            if ($t !== '' && ($t[0] === '{' || $t[0] === '[')) {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            }
        }
        return $raw;
    }

    /**
     * Derive anchor text from a URL (basename by default).
     */
    public static function deriveAnchorTextFromUrl(string $url): string
    {
        $parts = wp_parse_url($url);
        $path = $parts['path'] ?? '';
        $file = $path !== '' ? wp_basename(rawurldecode($path)) : ($parts['host'] ?? $url);
        return trim($file) !== '' ? $file : $url;
    }

    /**
     * Build an <a> tag with safe label.
     */
    public static function makeAnchor(string $url, ?string $text = null): string
    {
        $href = esc_url($url);
        $label = $text !== null && trim($text) !== ''
            ? wp_strip_all_tags($text)
            : static::deriveAnchorTextFromUrl($url);

        return sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
            $href,
            esc_html($label)
        );
    }


    /**
     * Recursively flatten any value into HTML, preferring user-facing fields
     * and skipping non-user keys like price/price_type.
     */
    public static function flattenToHtml($raw): string
    {
        $raw = static::maybeJsonDecode($raw);

        // STRING
        if (!is_array($raw)) {
            if (is_string($raw) && filter_var($raw, FILTER_VALIDATE_URL)) {
                return static::makeAnchor($raw);
            }
            return (string) $raw;
        }

        // APF-style object: prefer user-facing keys
        foreach (['display', 'formatted_value', 'formatted_label', 'user_value', 'value'] as $prefer) {
            if (array_key_exists($prefer, $raw) && $raw[$prefer] !== '' && $raw[$prefer] !== null) {
                return static::flattenToHtml($raw[$prefer]);
            }
        }

        // If values[] exists, flatten it. It might be a list of strings or objects with 'value'
        if (!empty($raw['values']) && is_array($raw['values'])) {
            $parts = [];
            foreach ($raw['values'] as $v) {
                if (is_array($v) && array_key_exists('value', $v)) {
                    $parts[] = static::flattenToHtml($v['value']);
                } else {
                    $parts[] = static::flattenToHtml($v);
                }
            }
            $parts = array_filter($parts, static fn($s) => $s !== '');
            return $parts ? implode(', ', $parts) : '';
        }

        // Special case: upload object { label: <url>, formatted_label: <name>, ... }
        if (isset($raw['label']) || isset($raw['formatted_label'])) {
            $labelVal = $raw['label'] ?? '';
            $textVal = $raw['formatted_label'] ?? '';
            if (is_string($labelVal) && filter_var($labelVal, FILTER_VALIDATE_URL)) {
                return static::makeAnchor($labelVal, is_string($textVal) && $textVal !== '' ? $textVal : null);
            }
            // fall through to generic flatten if not a URL
        }

        // GENERIC ARRAY: flatten while skipping non-user keys
        $parts = [];
        foreach ($raw as $k => $v) {
            // Skip known non-user keys
            if (is_string($k) && in_array(strtolower($k), static::$nonUserValueKeys, true)) {
                continue;
            }
            // Skip empty scalars like 0/none if they’re clearly price/meta noise
            if (is_string($v) && in_array(strtolower($v), ['none'], true)) {
                continue;
            }
            if ($v === 0 || $v === '0') {
                // only include zero if it's clearly the only meaningful value (we'll let the filter below drop it)
                continue;
            }
            $parts[] = static::flattenToHtml($v);
        }

        // Clean empty bits and join
        $parts = array_filter($parts, static fn($s) => $s !== '' && $s !== '0' && strtolower($s) !== 'none');
        return $parts ? implode(', ', $parts) : '';
    }


    /**
     * Convert raw meta into safe HTML (anchors allowed), using flattenToHtml
     * which now ignores price/price_type and prefers user text.
     */
    public static function valueToHtml($raw): string
    {
        $html = static::flattenToHtml($raw);
        $allowed = ['a' => ['href' => true, 'target' => true, 'rel' => true]];
        return trim(wp_kses($html, $allowed));
    }

    /**
     * Sanitize a prebuilt HTML string but keep <a>.
     */
    public static function sanitizeAnchors(string $html): string
    {
        $allowed = ['a' => ['href' => true, 'target' => true, 'rel' => true]];
        return trim(wp_kses($html, $allowed));
    }
}