@php
  // translators: %s: Quantity.
  $label = ! empty($args['product_name'])
    ? sprintf(esc_html__('%s quantity', 'woocommerce'), wp_strip_all_tags($args['product_name']))
    : esc_html__('Quantity', 'woocommerce');

  $is_readonly = !empty($readonly);
@endphp

<div class="quantity">
  @php do_action('woocommerce_before_quantity_input_field'); @endphp

  <label class="sr-only" for="{{ esc_attr($input_id) }}">
    {{ esc_html($label) }}
  </label>

  <div
    x-data="qtyComponent({
      readonly: {{ $is_readonly ? 'true' : 'false' }},
      min: {{ json_encode($min_value) }},
      max: {{ json_encode($max_value) }},   // 0 or '' means no max
      step: {{ json_encode($step ?: '1') }}
    })"
    class="flex shadow-sm rounded-md"
  >
    <button
      class="btn btn-outline btn-icon rounded-r-none shadow-none!"
      type="button"
      x-on:click="dec()"
      x-bind:disabled="decDisabled"
      x-bind:aria-disabled="decDisabled ? 'true' : 'false'"
    >
      <x-lucide-minus/>
      <span class="sr-only">{{ esc_html__('Decrease quantity', 'woocommerce') }}</span>
    </button>

    <input
      x-ref="input"
      type="{{ esc_attr($type) }}"
      @if($is_readonly) readonly="readonly" @endif
      id="{{ esc_attr($input_id) }}"
      class="{{ esc_attr(implode(' ', (array) $classes)) }} text-center !rounded-none !border-l-0 !border-r-0 shadow-none!"
      name="{{ esc_attr($input_name) }}"
      value="{{ esc_attr($input_value) }}"
      aria-label="{{ esc_attr__('Product quantity', 'woocommerce') }}"

      @if(in_array($type, ['text','search','tel','url','email','password'], true))
        size="4"
      @endif

      min="{{ esc_attr($min_value) }}"
      @if(0 < (int) $max_value) max="{{ esc_attr($max_value) }}" @endif

      @unless($is_readonly)
        step="{{ esc_attr($step) }}"
        placeholder="{{ esc_attr($placeholder) }}"
        inputmode="{{ esc_attr($inputmode) }}"
        autocomplete="{{ esc_attr(isset($autocomplete) ? $autocomplete : 'on') }}"
      @endunless
    />

    <button
      class="btn btn-outline btn-icon rounded-l-none shadow-none!"
      type="button"
      x-on:click="inc()"
      x-bind:disabled="incDisabled"
      x-bind:aria-disabled="incDisabled ? 'true' : 'false'"
    >
      <x-lucide-plus/>
      <span class="sr-only">{{ esc_html__('Increase quantity', 'woocommerce') }}</span>
    </button>
  </div>

  @php do_action('woocommerce_after_quantity_input_field'); @endphp
</div>

<script>
  document.addEventListener('alpine:init', () => {
    Alpine.data('qtyComponent', (opts) => ({
      // props
      readonly: !!opts.readonly,
      min: Number(opts.min ?? 0),
      max: (opts.max && Number(opts.max) > 0) ? Number(opts.max) : null, // null = no max
      step: Number(opts.step || 1),

      // state
      value: 0,

      get decDisabled() {
        if (this.readonly) return true
        return (this.value - this.step) < this.min
      },
      get incDisabled() {
        if (this.readonly) return true
        return this.max !== null && (this.value + this.step) > this.max
      },

      init() {
        // initialize from input
        const i = this.$refs.input
        this.value = Number(i.value || this.min || 1)

        // if anything else changes the input, sync our state
        i.addEventListener('change', () => {
          this.value = Number(i.value || this.min || 1)
        })
        i.addEventListener('input', () => {
          // keep buttons responsive while typing
          this.value = Number(i.value || this.min || 0)
        })
      },

      dec() {
        if (this.decDisabled) return
        const i = this.$refs.input
        if (!i.readOnly) {
          i.stepDown()
          i.dispatchEvent(new Event('change', { bubbles: true }))
          this.value = Number(i.value)
          i.focus()
        }
      },

      inc() {
        if (this.incDisabled) return
        const i = this.$refs.input
        if (!i.readOnly) {
          i.stepUp()
          i.dispatchEvent(new Event('change', { bubbles: true }))
          this.value = Number(i.value)
          i.focus()
        }
      },
    }))
  })
</script>