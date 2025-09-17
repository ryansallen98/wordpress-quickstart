<div class="rounded-lg bg-primary text-primary-foreground p-5 shadow-sm">
    <h3 class="text-base font-semibold">{!! __('Need help?', 'woocommerce') !!}</h3>
    <p class="mt-1 text-sm">{!! __('Questions about orders or deliveries? We’re here.', 'woocommerce') !!}</p>
    <div class="mt-4 flex gap-2">
        <a href="{{ $links['orders'] }}" class="btn btn-secondary btn-lg">{!! __('Order support', 'woocommerce') !!}</a>
        <a href="{{ $links['contact'] }}"
            class="btn btn-ghost btn-lg">{!! __('Contact us', 'woocommerce') !!}</a>
    </div>
</div>