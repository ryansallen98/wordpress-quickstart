@props([
  'name' => null,
  'searchable' => false,
  'value' => null,
  'maxHeight' => 'var(--radix-popover-content-available-height, 320px)',
])

<div
  x-data="selectComponent({
            name: @js($name),
            searchable: {{ $searchable ? 'true' : 'false' }},
            placeholder: null,
            value: @js($value),
            maxHeight: @js($maxHeight),
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
  {{-- Hidden input for forms --}}
  @if ($name)
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
