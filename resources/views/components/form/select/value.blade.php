@props([
  'placeholder' => __('Select…',
  'wordpress-quickstart'),
])

<div class="flex min-w-0 items-center gap-2 text-left">
  <span
    class="truncate"
    x-show="displayLabels().length"
    x-text="displayLabels()[0]"
  ></span>
  <span
    class="text-muted-foreground truncate"
    x-init="placeholder = @js($placeholder)"
    x-show="! displayLabels().length"
    x-text="placeholder"
  ></span>
</div>
