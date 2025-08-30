@props([
    // Behavior
    'href' => null,
    'type' => 'button',
    'disabled' => false,
    'loading' => false,
    'target' => null,
    'rel' => null,

    // Appearance
    'variant' => 'primary',
    'size' => 'md',
    'fullWidth' => false,
])

@php
    // Base styles
    $base = "cursor-pointer inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-all outline-none shrink-0
           focus-visible:ring-ring/50 focus-visible:ring-[3px] focus-visible:border-ring
           disabled:pointer-events-none disabled:opacity-50
           [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 [&_svg]:shrink-0
           no-underline!";

    // Variants
    $variants = [
        'primary' => 'bg-primary text-primary-foreground shadow-xs hover:bg-primary/90',
        'destructive' =>
            'bg-destructive text-white shadow-xs hover:bg-destructive/80 focus-visible:ring-destructive/20 dark:focus-visible:ring-destructive/40 dark:bg-destructive/60 dark:hover:bg-destructive/80',
        'outline' =>
            'border bg-background shadow-xs hover:bg-accent hover:text-accent-foreground dark:bg-input/30 dark:border-input dark:hover:bg-input/50',
        'secondary' => 'bg-secondary text-secondary-foreground shadow-xs hover:bg-secondary/80',
        'ghost' => 'hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50',
        'link' => 'text-primary underline-offset-4 hover:underline! bg-transparent shadow-none',
    ];

    // Sizes
    $sizes = [
        'sm' => 'h-8 rounded-md gap-1.5 px-3 has-[>svg]:px-2.5',
        'md' => 'h-9 px-4 py-2 has-[>svg]:px-3',
        'lg' => 'h-10 rounded-md px-6 has-[>svg]:px-4',
        'icon' => 'size-9 p-0',
    ];

    $width = $fullWidth ? 'w-full' : '';

    // Compose classes using TailwindMerge
    $composed = $tw->merge(
        $base,
        $variants[$variant] ?? $variants['primary'],
        $sizes[$size] ?? $sizes['md'],
        $width,
        $attributes->get('class'),
    );

    // aria/rel/data attributes
    $ariaDisabled = $disabled || $loading ? 'true' : null;
    $ariaBusy = $loading ? 'true' : null;
    $dataLoading = $loading ? 'true' : null;
    $relAttr = $target === '_blank' ? $rel ?? 'noopener noreferrer' : $rel;

    // Shared attribute bag
    $bag = $attributes->merge([
        'class' => $composed,
        'aria-disabled' => $ariaDisabled,
        'aria-busy' => $ariaBusy,
        'data-loading' => $dataLoading,
        'data-variant' => $variant,
        'data-size' => $size,
        'target' => $target,
        'rel' => $relAttr,
    ]);
@endphp

@if ($href)
    {{-- Anchor version --}}
    <a href="{{ $href }}" {{ $bag }} @if ($disabled || $loading) tabindex="-1" @endif>
        {{ $slot }}
    </a>
@else
    {{-- Button version --}}
    <button type="{{ $type }}" @if ($disabled || $loading) disabled @endif {{ $bag }}>
        {{ $slot }}
    </button>
@endif
