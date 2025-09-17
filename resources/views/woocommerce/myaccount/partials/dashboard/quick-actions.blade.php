@php
    $quickActions = [
        ['label' => __('Orders', 'woocommerce'), 'key' => 'orders', 'icon' => 'shopping-bag'],
        ['label' => __('Payment methods', 'woocommerce'), 'key' => 'payment', 'icon' => 'credit-card'],
        ['label' => __('Addresses', 'woocommerce'), 'key' => 'addresses', 'icon' => 'map-pin'],
        ['label' => __('Account details', 'woocommerce'), 'key' => 'account', 'icon' => 'user'],
        ['label' => __('Shop', 'woocommerce'), 'key' => 'shop', 'icon' => 'store'],
        ['label' => __('View cart', 'woocommerce'), 'key' => 'cart', 'icon' => 'shopping-cart'],
    ];
@endphp

<div class="rounded-lg bg-card border shadow-sm p-5">
    <h3 class="text-base font-semibold">{{ __('Quick actions', 'woocommerce') }}</h3>
    <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
        @foreach ($quickActions as $action)
            <a class="btn btn-outline text-left justify-start btn-lg" href="{{ $links[$action['key']] }}">
                <x-dynamic-component :component="'lucide-'.$action['icon']" class="w-4 h-4" aria-hidden="true" />
                {{ $action['label'] }}
            </a>
        @endforeach
    </div>
</div>