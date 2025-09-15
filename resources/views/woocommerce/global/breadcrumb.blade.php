@if (!empty($breadcrumb))
  @php($count = count($breadcrumb))

  <x-breadcrumbs>
    @foreach ($breadcrumb as $i => $crumb)
      @php([$label, $url] = [$crumb[0] ?? '', $crumb[1] ?? ''])

      <x-breadcrumbs.item>
        @if (!empty($url) && $i + 1 !== $count)
          <x-breadcrumbs.link :href="esc_url($url)">
            {!! esc_html($label) !!}
          </x-breadcrumbs.link>
        @else
          {{-- Last item (current page) or items without URLs --}}
          <x-breadcrumbs.page aria-current="page">
            {!! esc_html($label) !!}
          </x-breadcrumbs.page>
        @endif
      </x-breadcrumbs.item>

      @if ($i + 1 !== $count)
        <x-breadcrumbs.separator />
      @endif
    @endforeach
  </x-breadcrumbs>
@endif