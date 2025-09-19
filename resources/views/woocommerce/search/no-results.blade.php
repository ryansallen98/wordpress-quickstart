@php
  $q = isset($query) ? (string) $query : '';
@endphp

@if ($q !== '')
  <div class="">
    {{-- Plain text message (not part of the listbox navigation) --}}
    <p class="text-sm text-muted-foreground px-2 py-1.5" id="wcps-no-results-msg">
      {{ sprintf(
          esc_html__('No products found for “%s”.', 'wordpress-quickstart'),
          esc_html($q)
      ) }}
    </p>

    {{-- Only the link is an option inside the listbox --}}
    <ul role="listbox" aria-label="@lang('No results actions')" class="mt-2">
      <li role="presentation">
        <a
          href="{{ esc_url( add_query_arg('s', urlencode($q), home_url('/')) ) }}"
          role="option"
          id="wcps-option-search-site"
          aria-selected="false"
          class="py-1.5 px-2 flex items-center rounded-md w-full cursor-pointer
                 hover:bg-accent hover:text-accent-foreground no-underline!
                 focus:bg-accent focus:text-accent-foreground focus:outline-none
                 aria-selected:bg-accent aria-selected:text-accent-foreground text-sm"
        >
          {{ __('Search the whole site', 'wordpress-quickstart') }}
        </a>
      </li>
    </ul>
  </div>
@else
  <div class="p-1">
    <p class="text-sm text-muted-foreground">
      {{ __('No products found.', 'wordpress-quickstart') }}
    </p>
  </div>
@endif