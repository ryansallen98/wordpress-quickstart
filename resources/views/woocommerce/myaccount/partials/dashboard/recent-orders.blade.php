@php
  // simple column config
  $orderTableCols = [
    ['key' => 'order',  'label' => __('Order', 'woocommerce')],
    ['key' => 'date',   'label' => __('Date', 'woocommerce')],
    ['key' => 'total',  'label' => __('Total', 'woocommerce')],
    ['key' => 'status', 'label' => __('Status', 'woocommerce')],
  ];
@endphp

<div class="rounded-lg bg-card border shadow-sm border-b-0">
  <div class="px-5 py-4 border-b flex items-center justify-between">
    <h2 class="text-base font-semibold">{!! __('Recent orders', 'woocommerce') !!}</h2>
    <a href="{{ $links['orders'] }}" class="btn btn-link btn-sm h-auto p-0">{!! __('See all', 'woocommerce') !!}</a>
  </div>

  @if(!empty($recentOrders))
    <div class="overflow-x-auto">
      <table class="table">
        <thead class="thead">
          <tr class="border-b">
            @foreach($orderTableCols as $col)
              <th class="th">{{ $col['label'] }}</th>
            @endforeach
            <th class="th"></th>
          </tr>
        </thead>
        <tbody>
          @foreach($recentOrders as $order)
            @php
              /** @var \WC_Order $order */
              $status = 'wc-' . $order->get_status();
              [$label,$cls] = $statusPill($status);
              $items = $order->get_item_count() - $order->get_item_count_refunded();
            @endphp

            <tr>
              @foreach($orderTableCols as $col)
                <td class="td">
                  @switch($col['key'])
                    @case('order')
                      <a class="font-medium underline" href="{{ $order->get_view_order_url() }}">#{{ $order->get_order_number() }}</a>
                      <span class="text-muted-foreground"> · {{ $items }} items</span>
                      @break

                    @case('date')
                      {{ wc_format_datetime($order->get_date_created(), get_option('date_format')) }}
                      @break

                    @case('total')
                      {!! $order->get_formatted_order_total() !!}
                      @break

                    @case('status')
                      <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $cls }}">{{ $label }}</span>
                      @break
                  @endswitch
                </td>
              @endforeach

              <td class="td">
                <div class="flex justify-end gap-2">
                <a href="{{ $order->get_view_order_url() }}" class="btn btn-outline btn-sm">{!! __('View', 'woocommerce') !!}</a>
                @if($order->has_status(apply_filters('woocommerce_valid_order_statuses_for_reorder', ['completed','processing','on-hold'], $order)))
                  <a href="{{ wp_nonce_url(add_query_arg('order_again', $order->get_id(), wc_get_cart_url()), 'woocommerce-order_again') }}" class="btn btn-primary btn-sm">{!! __('Buy again', 'woocommerce') !!}</a>
                @endif
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="px-5 py-8 text-sm muted-foreground">
      {!! __('You don’t have any orders yet.', 'woocommerce') !!} <a class="underline" href="{{ $links['shop'] }}">{!! __('Start shopping', 'woocommerce') !!}</a>.
    </div>
  @endif
</div>