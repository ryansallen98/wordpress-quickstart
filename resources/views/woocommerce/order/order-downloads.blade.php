<section class="woocommerce-order-downloads">
  @isset($show_title)
    <h2 class="woocommerce-order-downloads__title">{{ __('Downloads', 'woocommerce') }}</h2>
  @endisset
    <div class="rounded-lg overflow-auto border border-b-0 shadow-sm">
        <table class="woocommerce-table woocommerce-table--order-downloads shop_table shop_table_responsive order_details table">
            <thead class="thead">
            <tr>
                @foreach (wc_get_account_downloads_columns() as $column_id => $column_name)
                @php 
                    $extraClass = $column_id === 'download-file' ? 'text-right' : '';
                @endphp

                <th class="{{ esc_attr($column_id) }} th {{ $extraClass }}"><span class="nobr">{{ esc_html($column_name) }}</span></th>
                @endforeach
            </tr>
            </thead>

            @foreach ($downloads as $download)
            <tr>
                @foreach (wc_get_account_downloads_columns() as $column_id => $column_name)
                    @php
                    $handled = has_action('woocommerce_account_downloads_column_' . $column_id);
                    $extraClass = $column_id === 'download-file' ? 'text-right' : '';
                    @endphp

                <td class="{{ esc_attr($column_id) }} td {{ $extraClass }}" data-title="{{ esc_attr($column_name) }}">
                    @if ($handled)
                    @php do_action('woocommerce_account_downloads_column_' . $column_id, $download); @endphp
                    @else
                    @switch($column_id)
                        @case('download-product')
                        @if (!empty($download['product_url']))
                            <a href="{{ esc_url($download['product_url']) }}">{{ esc_html($download['product_name']) }}</a>
                        @else
                            {!! esc_html($download['product_name']) !!}
                        @endif
                        @break

                        @case('download-file')
                        <a href="{!! esc_url($download['download_url']) !!}" class="woocommerce-MyAccount-downloads-file button alt">
                            {!! esc_html($download['download_name']) !!}
                        </a>
                        @break

                        @case('download-remaining')
                        {!! is_numeric($download['downloads_remaining']) ? esc_html($download['downloads_remaining']) : esc_html__('&infin;', 'woocommerce') !!}
                        @break

                        @case('download-expires')
                        @if (!empty($download['access_expires']))
                            @php
                            $ts = strtotime($download['access_expires']);
                            @endphp
                            <time datetime="{{ esc_attr(date('Y-m-d', $ts)) }}" title="{{ esc_attr($ts) }}">
                            {!! esc_html(date_i18n(get_option('date_format'), $ts)) !!}
                            </time>
                        @else
                            {{ __('Never', 'woocommerce') }}
                        @endif
                        @break
                    @endswitch
                    @endif
                </td>
                @endforeach
            </tr>
            @endforeach
        </table>
    </div>
</section>