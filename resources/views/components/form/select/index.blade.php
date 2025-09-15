@props([
  'name' => null,
  'searchable' => false,
  'value' => null,
  'options' => [],  
  'maxHeight' => 'var(--radix-popover-content-available-height, 320px)',
])

<div
  x-data="selectComponent({
            name: @js($name),
            searchable: {{ $searchable ? 'true' : 'false' }},
            placeholder: null,
            value: @js($value),
            maxHeight: @js($maxHeight),
            options: @js($options),
          })"
  class="relative inline-block"
  @keydown.escape.stop.prevent="close()"
  @click.outside="close()"
  @x-select:register.window="register($event.detail, $event.detail.__el)"
  @x-select:unregister.window="unregister($event.detail?.value)"
  x-init="
    $watch('selected', (v) => {
      if ($refs.hiddenInput) {
        $refs.hiddenInput.dispatchEvent(new Event('change', { bubbles: true }))
      }
    })
  "
>

{{-- Hidden form control for Woo/normal forms --}}
@if ($name && Str::startsWith($name, 'attribute_'))
  {{-- Woo requires a real <select name="attribute_*"> --}}
  <select
    x-ref="native"
    name="{{ $name }}"
    :data-attribute_name="name"
    data-show_option_none="yes"
    class="sr-only absolute -m-px h-px w-px overflow-hidden"
    @change.stop="selected = $event.target.value"
    x-init="
      $watch('selected', v => {
        if ($refs.native && String($refs.native.value) !== String(v)) {
          $refs.native.value = v ?? '';
          $refs.native.dispatchEvent(new Event('change', { bubbles: true }))
        }
      })
    "
  >
    <option value="">{{ __('Choose an option', 'woocommerce') }}</option>
    <!-- Build options from your registered items -->
    <template x-for="opt in options" :key="opt.value">
      <option
        :value="String(opt.value)"
        x-text="opt.label"
        :disabled="!!opt.disabled"
        :selected="String(selected) === String(opt.value)"
      ></option>
    </template>
  </select>
@elseif ($name)
  {{-- Not a Woo attribute: keep the hidden input for normal forms --}}
  <input
    x-ref="hiddenInput"
    type="hidden"
    name="{{ $name }}"
    :value="selected"
    {{ $attributes }}
  />
@endif

  {{ $slot }}
</div>
