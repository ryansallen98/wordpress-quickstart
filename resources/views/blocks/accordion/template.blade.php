@php
/**
* IMPORTANT: For ACF Blocks, read fields from the BLOCK, not the post.
* ACF 6+ lets you do get_fields( $block['id'] ) to fetch this block instance's data.
*/
$acfFields = [];
if (function_exists('get_fields')) {
// If ACF is older and doesn't support $block['id'], this will just return null.
$acfFields = get_fields($block['id']) ?: [];
}

// Fallback if for some reason the above is empty but $fields was injected from the PHP callback.
if (empty($acfFields) && isset($fields) && is_array($fields)) {
$acfFields = $fields;
}

// Top-level fields
$type = $acfFields['type'] ?? 'single'; // 'single' | 'multiple'
$rows = $acfFields['accordions'] ?? [];

// Common block props
$anchor = $block['anchor'] ?? null;
$className = $block['className'] ?? '';
$align = $block['align'] ?? ''; // 'wide' | 'full'
$id = $anchor ?: ($block['id'] ?? ('accordion_' . uniqid()));

$classes = trim('wpqs-accordion-block ' . $className . ($align ? " align{$align}" : ''));
@endphp

<x-accordion :type="$type" :id="$id" :class="$classes">
  @if (!empty($rows) && is_array($rows))
  @foreach ($rows as $row)
  @php
  // Repeater 'accordions' -> Group 'accordion' with subfields
  $item = $row['accordion'] ?? [];
  $level = (int)($item['level'] ?? 3);
  $isOpen = !empty($item['initially_open']);
  $title = (string)($item['title'] ?? '');
  $content = (string)($item['content'] ?? '');
  @endphp

  <x-accordion.item :open="$isOpen">
    <x-accordion.trigger :level="$level">
      {{ $title !== '' ? $title : 'Accordion item' }}
    </x-accordion.trigger>
    <x-accordion.content>
      {!! $content !!}
    </x-accordion.content>
  </x-accordion.item>
  @endforeach
  @else
  @if (!empty($is_preview))
  <div>
    Add rows to the Accordion field group to see items here.
  </div>
  @endif
  @endif
</x-accordion>