@php
  // translators: %s: Quantity.
  $label = !empty($args['product_name'])
    ? sprintf(esc_html__('%s quantity', 'woocommerce'), wp_strip_all_tags($args['product_name']))
    : esc_html__('Quantity', 'woocommerce');

  $is_readonly = !empty($readonly);
@endphp

<div class="quantity">
  @php do_action('woocommerce_before_quantity_input_field'); @endphp

  <label class="sr-only" for="{{ esc_attr($input_id) }}">
    {{ esc_html($label) }}
  </label>

  <div x-data="qtyComponent({
      readonly: {{ $is_readonly ? 'true' : 'false' }},
      min: {{ json_encode($min_value) }},
      max: {{ json_encode($max_value) }},   // 0 or '' means no max
      step: {{ json_encode($step ?: '1') }}
    })" class="flex shadow-sm rounded-md w-fit">
    <button class="btn btn-outline btn-icon rounded-r-none shadow-none! !border-r-0" type="button" x-on:click="dec()"
      x-bind:disabled="decDisabled" x-bind:aria-disabled="decDisabled ? 'true' : 'false'">
      <x-lucide-minus />
      <span class="sr-only">{{ esc_html__('Decrease quantity', 'woocommerce') }}</span>
    </button>

    <input x-ref="input" type="{{ esc_attr($type) }}" @if($is_readonly) readonly="readonly" @endif
      id="{{ esc_attr($input_id) }}"
      class="{{ esc_attr(implode(' ', (array) $classes)) }} text-center !rounded-none shadow-none! max-w-[50px] relative z-1"
      name="{{ esc_attr($input_name) }}" value="{{ esc_attr($input_value) }}"
      aria-label="{{ esc_attr__('Product quantity', 'woocommerce') }}" style="-moz-appearance: textfield;"
      x-on:keydown="onKeydown($event)" x-on:paste="onPaste($event)" x-on:input="onInput($event)" x-on:blur="onBlur()"
      x-on:wheel.prevent inputmode="numeric" pattern="[0-9]*" @if(in_array($type, ['text', 'search', 'tel', 'url', 'email', 'password'], true)) size="4" @endif min="{{ esc_attr($min_value) }}" @if(0 < (int) $max_value)
      max="{{ esc_attr($max_value) }}" @endif @unless($is_readonly) step="{{ esc_attr($step) }}"
        placeholder="{{ esc_attr($placeholder) }}" inputmode="{{ esc_attr($inputmode) }}"
      autocomplete="{{ esc_attr(isset($autocomplete) ? $autocomplete : 'on') }}" @endunless />

    <button class="btn btn-outline btn-icon rounded-l-none shadow-none! !border-l-0" type="button" x-on:click="inc()"
      x-bind:disabled="incDisabled" x-bind:aria-disabled="incDisabled ? 'true' : 'false'">
      <x-lucide-plus />
      <span class="sr-only">{{ esc_html__('Increase quantity', 'woocommerce') }}</span>
    </button>
  </div>

  @php do_action('woocommerce_after_quantity_input_field'); @endphp
</div>

@pushOnce('scripts')
<script>
  document.addEventListener('alpine:init', () => {
    Alpine.data('qtyComponent', (opts) => ({
      // props
      readonly: !!opts.readonly,
      min: Number(opts.min ?? 0),
      max: (opts.max && Number(opts.max) > 0) ? Number(opts.max) : null, // null = no max
      step: Math.max(1, Number(opts.step || 1)), // enforce integer steps ≥ 1

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
        const i = this.$refs.input
        this.value = this._toInt(i.value ?? this.min ?? 1)
        // Sync if other scripts change it (Woo, etc.)
        i.addEventListener('change', () => { this.value = this._toInt(i.value ?? this.min ?? 1) })
        i.addEventListener('input', () => { this.value = this._toInt(i.value ?? this.min ?? 0) })
      },

      // --- Integer-only enforcement ---
      onKeydown(e) {
        if (this.readonly) return
        const allowed = [
          'Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'Home', 'End'
        ]
        // Allow Ctrl/Meta combos (copy/paste, select all)
        if (e.ctrlKey || e.metaKey) return
        if (allowed.includes(e.key)) return
        // Digits only
        if (/^\d$/.test(e.key)) return
        // Optional minus at start only if min < 0
        if (e.key === '-' && this.min < 0) {
          const el = e.target
          const caretAtStart = el.selectionStart === 0
          const noMinusYet = String(el.value).indexOf('-') === -1
          if (caretAtStart && noMinusYet) return
        }
        // Block everything else (e, ., , +, etc.)
        e.preventDefault()
      },

      onPaste(e) {
        if (this.readonly) return
        const text = (e.clipboardData || window.clipboardData).getData('text')
        const cleaned = this._clean(text)
        if (cleaned === '') {
          e.preventDefault()
          return
        }
        // Let it paste, then sanitize in onInput
        // (Alternatively, preventDefault and insert cleaned manually)
      },

      onInput(e) {
        if (this.readonly) return
        const el = e.target
        const cleaned = this._clean(el.value)
        if (cleaned !== el.value) {
          const pos = el.selectionStart
          el.value = cleaned
          // Try to keep caret position reasonable
          el.setSelectionRange(pos - 1, pos - 1)
        }
        // Do not clamp yet—let user type; store numeric snapshot
        const n = this._toInt(cleaned)
        this.value = isNaN(n) ? this.value : n
      },

      onBlur() {
        // On blur, normalise to step + clamp to min/max
        const i = this.$refs.input
        let n = this._toInt(i.value)
        if (isNaN(n)) n = this.min || 1
        n = this._snapToStep(this._clamp(n))
        i.value = String(n)
        this.value = n
        i.dispatchEvent(new Event('change', { bubbles: true }))
      },

      // --- Buttons (unchanged) ---
      dec() {
        if (this.decDisabled) return
        const i = this.$refs.input
        if (!i.readOnly) {
          i.stepDown()
          this._postStep(i)
        }
      },
      inc() {
        if (this.incDisabled) return
        const i = this.$refs.input
        if (!i.readOnly) {
          i.stepUp()
          this._postStep(i)
        }
      },
      _postStep(i) {
        // Make sure it’s integer + snapped
        let n = this._toInt(i.value)
        n = this._snapToStep(this._clamp(n))
        i.value = String(n)
        this.value = n
        i.dispatchEvent(new Event('change', { bubbles: true }))
        i.focus()
      },

      // --- helpers ---
      _toInt(v) {
        const m = String(v ?? '').match(/^-?\d+/)
        return m ? parseInt(m[0], 10) : NaN
      },
      _clean(v) {
        v = String(v ?? '')
        // Keep optional leading minus if min < 0
        const allowMinus = this.min < 0 && v.startsWith('-')
        const digits = v.replace(/\D+/g, '')
        return allowMinus ? ('-' + digits) : digits
      },
      _clamp(n) {
        if (this.max !== null) n = Math.min(n, this.max)
        n = Math.max(n, this.min ?? n)
        return n
      },
      _snapToStep(n) {
        // Snap to the nearest lower step from the base (min or 0)
        const base = Number.isFinite(this.min) ? this.min : 0
        const diff = n - base
        const snapped = base + Math.floor(diff / this.step) * this.step
        return snapped
      },
    }))
  })
</script>
@endpushOnce