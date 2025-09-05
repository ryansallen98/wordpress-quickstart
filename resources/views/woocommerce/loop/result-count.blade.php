@php
  if (! defined('ABSPATH')) { exit; }

  // Vars come from Woo: $total, $per_page, $current, $orderedby
  $total     = (int) ($total ?? 0);
  $per_page  = (int) ($per_page ?? 0);
  $current   = (int) ($current ?? 1);
  $orderedby = $orderedby ?? '';
@endphp

@if ($total > 0)
  <p
    class="text-sm text-muted-foreground"
    role="alert"
    aria-relevant="all"
    @if (! empty($orderedby) && $total > 1) data-is-sorted-by="true" @endif
  >
    @if ($total === 1)
      {{ __('Showing the single result', 'woocommerce') }}
    @elseif ($total <= $per_page || $per_page === -1)
      @php
        $orderedby_placeholder = empty($orderedby)
          ? '%2$s'
          : '<span class="screen-reader-text">%2$s</span>';
      @endphp
      {!! sprintf(
        /* translators: 1: total results 2: sorted by */
        _n('Showing all %1$d result', 'Showing all %1$d results', $total, 'woocommerce') . $orderedby_placeholder,
        $total,
        esc_html($orderedby)
      ) !!}
    @else
      @php
        $first = ($per_page * $current) - $per_page + 1;
        $last  = min($total, $per_page * $current);
        $orderedby_placeholder = empty($orderedby)
          ? '%4$s'
          : '<span class="screen-reader-text">%4$s</span>';
      @endphp
      {!! sprintf(
        /* translators: 1: first result 2: last result 3: total results 4: sorted by */
        _nx('Showing %1$d–%2$d of %3$d result', 'Showing %1$d–%2$d of %3$d results', $total, 'with first and last result', 'woocommerce') . $orderedby_placeholder,
        $first,
        $last,
        $total,
        esc_html($orderedby)
      ) !!}
    @endif
  </p>
@endif