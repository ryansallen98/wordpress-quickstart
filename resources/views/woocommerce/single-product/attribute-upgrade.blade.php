@php
/** @var \WC_Product_Variable $product */
/** @var string $label */
/** @var string $selected */
/** @var array<int,array{ term_name:string, term_slug:string, variation_id:int, price:float, attributes:array<string,string> }> $rows */
/** @var array $extras */

$defaultIndex = 0;
foreach ($rows as $i => $row) {
  if (sanitize_title($row['term_slug']) === sanitize_title($selected)) { $defaultIndex = $i; break; }
}
$default = $rows[$defaultIndex] ?? $rows[0];
$bypassNonce = wp_create_nonce('wc_attr_upgrade_add');

/**
 * Recursively render hidden inputs for scalars & arrays (supports a[b][c] and a[]).
 */
$renderHidden = function (string $name, $value) use (&$renderHidden) {
  if (is_array($value)) {
    foreach ($value as $k => $v) {
      $child = is_int($k) ? "{$name}[]" : "{$name}[{$k}]";
      $renderHidden($child, $v);
    }
  } else {
    echo '<input type="hidden" name="' . esc_attr($name) . '" value="' . esc_attr((string)$value) . '">';
  }
};

/**
 * Build a map of term_slug => variation image HTML (fallback to parent image).
 */
$variationImageHtmlBySlug = [];
$parentImageHtml = $product->get_image('medium_large', ['class' => 'mx-auto h-full w-full object-contain rounded-lg shadow-lg']);
foreach ($rows as $row) {
  $html = '';
  if (!empty($row['variation_id'])) {
    $vp = wc_get_product((int) $row['variation_id']);
    if ($vp instanceof \WC_Product_Variation) {
      $imgId = $vp->get_image_id();
      if ($imgId) {
        $html = wp_get_attachment_image($imgId, 'medium_large', false, ['class' => 'mx-auto h-full w-full object-contain rounded-lg shadow-lg']);
      }
    }
  }
  if (!$html) {
    // If variation had no image, fall back to parent product image
    $html = $parentImageHtml;
  }
  $variationImageHtmlBySlug[sanitize_title($row['term_slug'])] = $html;
}

// Initial image = image for default selection (pre-sanitized key)
$initialSlug = sanitize_title($default['term_slug'] ?? '');
$initialImageHtml = $variationImageHtmlBySlug[$initialSlug] ?? $parentImageHtml;
@endphp

<?php do_action('woocommerce_before_single_product'); ?>

<div class="md:h-[100dvh] overflow-auto flex flex-col justify-center items-center py-8">
  <div class="container mx-auto px-4 py-8" x-data="wcAttrUpgrade()" x-cloak>
    <div class="mx-auto w-full max-w-3xl">
      <div class="grid gap-8 md:grid-cols-2 items-center">
        <div id="wc-au-image-wrap">
          {!! $initialImageHtml !!}
        </div>
        <div>
          <h2 class="text-2xl font-semibold tracking-tight">
            {{ sprintf(__('Go Big and Upgrade your %s', 'sage'), $label) }}
          </h2>

          <p class="mt-2 text-sm text-muted-foreground">
            {{ __('Choose an option below, then continue. We’ll add your selection to the cart.', 'sage') }}
          </p>

          {{-- Post directly to Woo’s add-to-cart handler --}}
          <form
            method="post"
            action="{{ esc_url( add_query_arg('add-to-cart', $product->get_id(), get_permalink($product->get_id()) ) ) }}"
            class="mt-6 space-y-4"
            enctype="application/x-www-form-urlencoded"
          >
            {{-- Required Woo fields --}}
            <input type="hidden" name="add-to-cart" value="{{ esc_attr($product->get_id()) }}">
            <input type="hidden" name="product_id"  value="{{ esc_attr($product->get_id()) }}">
            <input type="hidden" name="quantity"    value="1">

            {{-- BYPASS the upsell interceptor when submitting from this page --}}
            <input type="hidden" name="wc_attr_upgrade_bypass" value="1">
            <input type="hidden" name="wc_attr_upgrade_nonce" value="{{ esc_attr($bypassNonce) }}">

            {{-- Variation fields (Alpine rewrites these on change) --}}
            <input type="hidden" name="variation_id" id="wc-au-variation-id" value="{{ (int) $default['variation_id'] }}">
            <div id="wc-au-attr-hidden">
              @foreach(($default['attributes'] ?? []) as $attrKey => $attrVal)
                <input type="hidden" name="{{ esc_attr($attrKey) }}" value="{{ esc_attr($attrVal) }}">
              @endforeach
            </div>

            {{-- Restore ALL extra fields captured from the original product form (incl. APF textboxes) --}}
            <div id="wc-au-extras-hidden">
              @if(!empty($extras))
                @foreach($extras as $ek => $ev)
                  {!! $renderHidden($ek, $ev) !!}
                @endforeach
              @endif
            </div>

            <div class="grid gap-3">
              @foreach ($rows as $i => $row)
                @php $isCurrent = sanitize_title($row['term_slug']) === sanitize_title($selected); @endphp

                <label
                  for="wc-au-choice-{{ $i }}"
                  class="group relative flex cursor-pointer items-center justify-between rounded-xl border border-input bg-card p-4 shadow-sm transition-colors hover:bg-accent/50 focus-within:ring-2 focus-within:ring-ring"
                >
                  <div class="flex items-start gap-3">
                    <span class="relative flex h-5 w-5 items-center justify-center">
                      <input
                        type="radio"
                        name="upgrade_choice"
                        id="wc-au-choice-{{ $i }}"
                        class="peer sr-only"
                        value="{{ esc_attr($row['term_slug']) }}"
                        x-model="selectedSlug"
                        {{ $i === $defaultIndex ? 'checked' : '' }}
                      />
                      <span class="h-5 w-5 rounded-full border border-input bg-background shadow-sm transition-all peer-checked:border-primary peer-checked:ring-4 peer-checked:ring-primary/20"></span>
                      <span class="pointer-events-none absolute inset-0 rounded-full opacity-0 peer-focus:opacity-100 peer-focus:ring-2 peer-focus:ring-ring"></span>
                    </span>

                    <div class="space-y-1">
                      <div class="text-sm font-medium leading-none">
                        {{ $row['term_name'] }}
                        @if($isCurrent)
                          <em class="ml-1 text-xs font-normal text-muted-foreground">({{ __('current', 'sage') }})</em>
                        @endif
                      </div>
                      <div class="text-xs text-muted-foreground">{{ $label }}</div>
                    </div>
                  </div>

                  <div class="ml-4 flex items-center gap-2">
                    <span class="text-sm font-semibold tabular-nums">{!! wc_price($row['price']) !!}</span>
                    <span class="hidden text-xs text-muted-foreground group-hover:inline">{{ __('Select', 'sage') }} →</span>
                  </div>
                </label>
              @endforeach
            </div>

            <button
              type="submit"
              class="btn btn-primary btn-lg w-full"
            >
              {{ __('Continue', 'sage') }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  function wcAttrUpgrade() {
    const options = @json($rows);
    const selectedSlugDefault = @json($default['term_slug']);
    const imagesBySlug = @json($variationImageHtmlBySlug);

    function rebuildHidden(sel) {
      const wrap = document.getElementById('wc-au-attr-hidden');
      const vid  = document.getElementById('wc-au-variation-id');
      if (!wrap || !vid || !sel) return;

      vid.value = sel.variation_id || '';
      wrap.innerHTML = '';
      if (sel.attributes) {
        Object.entries(sel.attributes).forEach(([k, v]) => {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = k;
          input.value = v;
          wrap.appendChild(input);
        });
      }
    }

    function swapImageForSlug(slug) {
      const wrap = document.getElementById('wc-au-image-wrap');
      if (!wrap) return;
      const key = (slug || '').toString().toLowerCase().replace(/\s+/g, '-');
      const html = imagesBySlug[key] || '';
      if (html) {
        wrap.innerHTML = html;
      }
    }

    return {
      options,
      selectedSlug: selectedSlugDefault,
      get selected() {
        return this.options.find(o => o.term_slug === this.selectedSlug) || this.options[0];
      },
      init() {
        rebuildHidden(this.selected);
        swapImageForSlug(this.selectedSlug);
        this.$watch('selectedSlug', () => {
          rebuildHidden(this.selected);
          swapImageForSlug(this.selectedSlug);
        });
      },
    };
  }
</script>

<?php do_action('woocommerce_after_single_product'); ?>