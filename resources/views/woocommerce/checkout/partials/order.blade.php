<div class="flex flex-col gap-4">
  @foreach ($items as $item)
    <div class="flex items-start justify-between gap-4">
      {{-- LEFT: thumbnail --}}
      <div class="relative hidden sm:block">
        <div
          class="bg-muted h-10 w-10 shrink-0 overflow-hidden rounded-lg shadow-md sm:h-14 sm:w-14 lg:h-20 lg:w-20"
        >
          {!! $item->thumbnail !!}
        </div>
        <span
          class="bg-primary text-primary-foreground absolute -top-2 -left-2 inline-flex items-center justify-center rounded-full px-2 py-1 text-xs font-semibold shadow-sm"
        >
          {{ $item->quantity }}
        </span>
      </div>

      {{-- MIDDLE: product info --}}
      <div class="min-w-0 flex-1">
        <div class="leading-snug font-medium">
          {!! $item->name !!}
        </div>

        @if (isset($order) && $order instanceof \WC_Order)
          @php
            do_action(
              'woocommerce_order_item_meta_start',
              $item->id,
              $item->_wc_item,
              $order,
              false,
            );
          @endphp
        @endif

        @if (! empty($item->attributes))
          <ul
            class="text-muted-foreground mt-1 list-inside list-disc space-y-0.5 text-sm font-medium"
          >
            @foreach ($item->attributes as $attr)
              <li>
                <span class="text-muted-foreground">
                  {{ $attr['label'] }}:
                </span>
                {{ $attr['value'] }}
              </li>
            @endforeach
          </ul>
        @elseif ($item->short_description)
          <div class="text-muted-foreground mt-1 text-sm">
            {{ $item->short_description }}
          </div>
        @endif

        @if (isset($order) && $order instanceof \WC_Order)
          @php
            do_action(
              'woocommerce_order_item_meta_end',
              $item->id,
              $item->_wc_item,
              $order,
              false,
            );
          @endphp
        @endif
      </div>

      {{-- RIGHT: line subtotal --}}
      <div class="shrink-0 text-right text-lg font-bold">
        {!! $item->subtotal !!}
        @if ($item->quantity > 1)
          <div class="text-muted-foreground mt-1 text-sm font-normal">
            {{ $item->quantity }} × {!! $item->unit_price !!}
          </div>
        @endif
      </div>
    </div>
  @endforeach
</div>
