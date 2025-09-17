@php
    $stats = [
        [
            'label' => __('Total orders', 'woocommerce'),
            'value' => $totalOrders,
            'link' => [
                'url' => $links['orders'],
                'text' => __('View all', 'woocommerce')
            ],
        ],
        [
            'label' => __('Open', 'woocommerce'),
            'value' => $openCount,
            'extra' => ($statusCounts['wc-processing'] ?? 0) . ' processing',
        ],
        [
            'label' => __('Completed', 'woocommerce'),
            'value' => $statusCounts['wc-completed'] ?? 0,
        ],
        [
            'label' => __('Addresses', 'woocommerce'),
            'value' => 2,
            'link' => [
                'url' => $links['addresses'],
                'text' => __('Manage', 'woocommerce')
            ],
        ],
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    @foreach ($stats as $stat)
        <div class="rounded-lg bg-card border shadow-sm p-4">
            <p class="text-xs text-muted-foreground">{{ $stat['label'] }}</p>
            <p class="mt-1 text-2xl font-semibold">{{ $stat['value'] }}</p>
            @if (!empty($stat['link']))
                <a href="{{ $stat['link']['url'] }}" class="btn btn-link btn-sm p-0 h-auto text-sm mt-2">{{ $stat['link']['text'] }}</a>
            @endif
            @if (!empty($stat['extra']))
                <p class="mt-2 text-xs text-muted-foreground">{{ $stat['extra'] }}</p>
            @endif
        </div>
    @endforeach
</div>