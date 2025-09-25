<?php
/**
 * Theme root: flexible.php (ACFE Dynamic Render)
 * Minimal payload to Blade: only essentials + sub-field values.
 */

// --- Guard & basics ---------------------------------------------------------
$layout     = isset($layout) && is_array($layout) ? $layout : [];
$is_preview = isset($is_preview) ? (bool) $is_preview : false;
$post_id    = isset($post_id) ? $post_id : get_the_ID();

$layout_name = $layout['name'] ?? ( function_exists('get_row_layout') ? get_row_layout() : '' );
$slug        = sanitize_title( (string) $layout_name );
$index       = function_exists('get_row_index') ? get_row_index() : null;

// --- helpers ---------------------------------------------------------------
$not_empty = static function($v) {
  if ($v === null) return false;
  if (is_string($v) && trim($v) === '') return false;
  if (is_array($v) && empty($v)) return false;
  return true;
};

// Collect only sub-field VALUES for this row (flattened)
$values = [];
if ($slug && !empty($layout['sub_fields']) && is_array($layout['sub_fields'])) {
  foreach ($layout['sub_fields'] as $sub) {
    if (empty($sub['name'])) continue;
    $name  = $sub['name'];
    $value = get_sub_field($name);
    if ($not_empty($value)) {
      $values[$name] = $value;
    }
  }
}

// Optional: basic post meta the dev may care about
$_meta = [
  'slug'       => $slug,
  'index'      => $index,
  'is_preview' => $is_preview,
  'post'       => [
    'id'        => $post_id,
    'title'     => get_the_title($post_id),
    'permalink' => get_permalink($post_id),
  ],
];

// --- Final payload ----------------------------------------------------------
// Expose flattened subfields as top-level vars (e.g. $slides) + a $_meta bag.
$payload = $values + ['_meta' => $_meta];

// Resolve view: resources/views/flexible/{slug}.blade.php
$view_name = "flexible.$slug";

if (function_exists('\Roots\view')) {
  if (\Roots\view()->exists($view_name)) {
    echo \Roots\view($view_name, $payload + ['context' => $payload])->render();
  } else {
    echo "<!-- Flexible view not found: {$view_name} -->";
  }
} else {
  // PHP fallback (if Blade not available)
  $php_fallback = get_stylesheet_directory() . "/resources/views/flexible/{$slug}.php";
  if (file_exists($php_fallback)) {
    extract($payload, EXTR_SKIP);
    include $php_fallback;
  } else {
    echo "<!-- Blade not loaded and no PHP fallback for {$slug} -->";
  }
}