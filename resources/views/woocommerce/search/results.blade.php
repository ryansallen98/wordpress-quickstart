@php
  /** @var array<int, array{key:string,heading:string,items:array<int,array{url:string,label:string,image?:string}>,total?:int,view_all?:string}> $groups */
  $groups = $groups ?? [];
  $query = $query ?? '';

  // Filter visible groups and decide whether to show headings
  $visibleGroups = array_values(array_filter($groups, fn($g) => !empty($g['items'])));
  $visibleCount = count($visibleGroups);
  $showHeadings = $visibleCount > 1;
@endphp

@if ($visibleCount === 0)
  <p class="text-sm p-2 text-muted-foreground">{{ __('No results found.', 'wordpress-quickstart') }}</p>
@else
  <div class="space-y-3" role="listbox" aria-label="@lang('Search results')" x-ignore>
    @foreach ($groups as $gIndex => $group)
      @continue(empty($group['items']))

      @php
        $heading_id = 'wcps-group-' . e($group['key']) . '-' . $gIndex;
      @endphp

      <div role="group" @if($showHeadings) aria-labelledby="{{ $heading_id }}" @endif class="border-b last:border-b-0 pb-2 last:pb-0">
        {{-- Group heading --}}
        @if($showHeadings)
          <p id="{{ $heading_id }}" class="text-muted-foreground px-2 py-1.5 text-xs">
            {{ $group['heading'] }}
          </p>
        @endif

        <ul class="text-sm">
          @foreach ($group['items'] as $i => $item)
            <li role="presentation">
              <a href="{{ esc_url($item['url']) }}" role="option" id="wcps-option-{{ $gIndex }}-{{ $i }}"
                aria-selected="false" class="py-1.5 px-2 flex items-center gap-2 rounded-md w-full cursor-pointer
               hover:bg-accent hover:text-accent-foreground no-underline!
               focus:bg-accent focus:text-accent-foreground focus:outline-none
               aria-selected:bg-accent aria-selected:text-accent-foreground">
                {{-- Image is optional --}}
                @if (!empty($item['image']))
                  <img src="{{ $item['image'] }}" alt="" class="w-8 h-8 object-cover rounded" loading="lazy" />
                @endif
                <div>{!! wp_kses_post($item['label']) !!}</div>
              </a>
            </li>
          @endforeach
        </ul>

        {{-- “View all X products” only for the Products group when more exist --}}
@if (
  ($group['key'] ?? '') === 'products'
  && !empty($group['total'])
  && $group['total'] > count($group['items'])
  && !empty($group['view_all'])
)
  <ul role="presentation" class="text-sm mt-3">
    <li role="presentation">
      <a
        href="{{ esc_url($group['view_all']) }}"
        role="option"
        id="wcps-option-viewall-{{ $gIndex }}"
        aria-selected="false"
        class="py-1.5 px-2 flex items-center gap-2 rounded-md w-full cursor-pointer
               hover:bg-accent hover:text-accent-foreground no-underline!
               focus:bg-accent focus:text-accent-foreground focus:outline-none
               aria-selected:bg-accent aria-selected:text-accent-foreground justify-center"
      >
        {{ sprintf(__('View all %d products', 'wordpress-quickstart'), (int) $group['total']) }}
      </a>
    </li>
  </ul>
@endif
      </div>
    @endforeach
  </div>
@endif