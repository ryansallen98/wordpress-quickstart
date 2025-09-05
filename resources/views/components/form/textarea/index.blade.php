@props([
  'id' => 'input-' . uniqid(),
  'name' => null,
  'label' => null,
  'value' => null,
  'type' => 'text',
  'description' => null,
  'placeholder' => null,
  'required' => false,
  'minlength' => null,
  'maxlength' => null,
  'autocomplete' => null,
  'disabled' => false,
  'readonly' => false,
  'fullwidth' => false,
  'priority' => 10,
  'wrapperclass' => null,
  'labelclass' => null,
  'class' => null,
  'iswoocommerce' => false,
])

@php
  // If name is not set, use id as name
  $name = $name ?: $id;

  // Base input styles
  $baseInputClass = 'file:text-foreground placeholder:text-muted-foreground 
      selection:bg-primary selection:text-primary-foreground dark:bg-input/30 
      border-input flex min-h-32 min-w-0 rounded-md border bg-transparent px-3 py-1 
      text-base shadow-xs transition-[color,box-shadow] outline-none file:inline-flex 
      file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium 
      disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 
      md:text-sm focus-visible:border-ring focus-visible:ring-ring/50 
      focus-visible:ring-[3px] aria-invalid:ring-destructive/20 
      dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive';

  $wooInputClass = 'input-text group-[.woocommerce-validated]:border-success';

  // Merge all input classes
  $mergedInputClass = $tw->merge($iswoocommerce ? $wooInputClass : '', $baseInputClass, $fullwidth ? 'w-full' : '', $class);

  // Base wrapper styles
  $baseWrapperClass = 'flex flex-col gap-2';
  $wooWrapperClass = 'form-row';

  // Merge all wrapper classes
  $mergedWrapperClass = $tw->merge('group', $iswoocommerce ? $wooWrapperClass : '', $baseWrapperClass, $wrapperclass);

  // Base label class
  $baseLabelClass = 'flex items-center gap-2 text-sm leading-none font-medium select-none mt-2';
  $wooLabelClass = 'group-[.woocommerce-invalid]:text-destructive';

  // Merge all label classes
  $mergedLabelClass = $tw->merge($iswoocommerce ? $wooLabelClass : '', $baseLabelClass, $labelclass);
@endphp

<div
  id="{{ $id }}_field"
  data-priority="{{ $priority }}"
  class="{{ $mergedWrapperClass }}"
>
  <label for="{{ $id }}" class="{{ $mergedLabelClass }}">{{ $label }}</label>

  <div class="relative flex items-center">
    <textarea
      id="{{ $id }}"
      name="{{ $name }}"
      type="{{ $type }}"
      @if($value !== '') value="{{ $value }}" @endif
      @if($placeholder !== '') placeholder="{{ $placeholder }}" @endif
      @if($required !== '') required @endif
      @if($minlength !== '') minlength="{{ $minlength }}" @endif
      @if($maxlength !== '') maxlength="{{ $maxlength }}" @endif
      @if($autocomplete !== '') autocomplete="{{ $autocomplete }}" @endif
      @if($description !== '') aria-describedby="{{ $id }}_description" @endif
      class="{{ $mergedInputClass }}"
    ></textarea>
  </div>

  @if ($description !== '')
    <p id="{{ $id }}_description" class="text-muted-foreground text-xs">
      {{ $description }}
    </p>
  @endif
</div>
