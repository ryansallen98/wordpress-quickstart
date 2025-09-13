<header class="sticky top-0 z-50">
  <div class="bg-sidebar border-b">
    <div class="border-b">
      <div class="container mx-auto p-4 flex items-end justify-between">
        <a class="brand" href="{{ home_url('/') }}">

        </a>
        <div class="flex flex-row gap-2 items-end">
          <div class="w-full">
            <label class="sr-only" for="header-search">
              {{ __('Search', 'wordpress-quickstart') }}
            </label>
            <input class="input-text min-w-180" type="text" id="header-search" placeholder="Search..." />
          </div>
          <div class="flex flex-row">
            <x-theme.toggle />
            @if(class_exists('WooCommerce'))
              <a class="btn btn-ghost btn-icon" href="{{ get_permalink(get_option('woocommerce_myaccount_page_id')) }}">
                <x-heroicon-s-user class="size-5" />
                <span class="sr-only">{{ __('Account', 'wordpress-quickstart') }}</span>
              </a>
              <a class="btn btn-ghost btn-icon" href="{{ get_permalink(get_option('woocommerce_cart_page_id')) }}">
                <x-heroicon-s-shopping-bag class="size-5" />
                <span class="sr-only">{{ __('Cart', 'wordpress-quickstart') }}</span>
              </a>
            @endif
          </div>
        </div>
      </div>
    </div>
    <div class="container mx-auto p-2 py-4 flex">
      @include('partials.primary-navigation')
    </div>
  </div>
</header>


{{-- @php
// Normalize + shrink to a small array so printing won't explode memory
$shrink = function ($items, $depth = 2) use (&$shrink) {
$out = [];

// Support arrays, collections, stdClass
if ($items instanceof \Traversable) {
$items = iterator_to_array($items);
}

foreach ((array) $items as $it) {
$a = is_array($it) ? $it : (array) $it;

$node = [
'id' => $a['id'] ?? $a['ID'] ?? null,
'title' => $a['label'] ?? $a['title'] ?? '',
'url' => $a['url'] ?? '',
'target' => $a['target'] ?? '',
'rel' => $a['rel'] ?? '',
'active' => (bool) ($a['active'] ?? false),
// Include ACF if you attached it in the composer; otherwise this will be []
'acf' => isset($a['acf']) && is_array($a['acf']) ? $a['acf'] : [],
];

if ($depth > 0 && !empty($a['children'])) {
$node['children'] = $shrink($a['children'], $depth - 1);
}

$out[] = $node;
}

return $out;
};

$small = $shrink($mainMenu, 2); // limit to 2 levels
@endphp

<pre>{!! htmlspecialchars(var_export($small, true)) !!}</pre> --}}