@php
  $total = $total ?? wc_get_loop_prop('total_pages');
  $current = $current ?? wc_get_loop_prop('current_page');
  $base = $base ?? esc_url_raw(str_replace(999999999, '%#%', remove_query_arg('add-to-cart', get_pagenum_link(999999999, false))));
  $format = $format ?? '';

  if ($total <= 1) {
    return;
  }

  $links = paginate_links([
    'base' => $base,
    'format' => $format,
    'add_args' => false,
    'current' => max(1, $current),
    'total' => $total,
    'prev_text' => __('Previous', 'woocommerce'),
    'next_text' => __('Next', 'woocommerce'),
    'type' => 'array',
    'end_size' => 3,
    'mid_size' => 3,
  ]);
@endphp

<div class="w-full flex justify-center items-center">
  <x-pagination>
    <x-pagination.content>
      @foreach ($links as $link)
        @php
          $isActive = str_contains($link, 'current');
          $isPrev = str_contains($link, 'prev');
          $isNext = str_contains($link, 'next');
          $isDots = str_contains($link, 'dots');
          // Extract href safely (may not exist for current/dots).
          preg_match('/href=["\']([^"\']+)["\']/', $link, $m);
          $href = $m[1] ?? '#';
        @endphp

        <x-pagination.item>
          @if ($isDots)
            <x-pagination.ellipsis />
          @elseif ($isPrev)
            <x-pagination.prev href="{{ $href }}" />
          @elseif ($isNext)
            <x-pagination.next href="{{ $href }}" />
          @else
            <x-pagination.link href="{{ $href }}" :is-active="$isActive">
              {!! strip_tags($link) !!}
            </x-pagination.link>
          @endif
        </x-pagination.item>
      @endforeach
    </x-pagination.content>
  </x-pagination>
</div>
