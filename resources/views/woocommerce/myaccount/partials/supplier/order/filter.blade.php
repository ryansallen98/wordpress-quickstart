<form method="get" action="{{ esc_url($endpoint_url) }}" class="flex flex-row gap-4 w-full items-end border-b py-2 px-4 overflow-auto whitespace-nowrap">
    <div class="min-w-[120px]">
        <label class="input-label">
            {{ esc_html__('Status', 'wc-supplier-manager') }}
        </label>
        <select name="status" class="input-select">
            @foreach ($statuses as $k => $label)
                <option value="{{ esc_attr($k) }}" {!! selected($c['status'], $k, false) !!}>
                    {{ esc_html($label) }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="input-label">
            {{ esc_html__('From', 'wc-supplier-manager') }}
        </label>
        <input type="date" name="wcsm_from" value="{{ esc_attr($c['wcsm_from']) }}" class="input-text" />
    </div>

    <div>
        <label class="input-label">
            {{ esc_html__('To', 'wc-supplier-manager') }}
        </label>
        <input type="date" name="wcsm_to" value="{{ esc_attr($c['wcsm_to']) }}" class="input-text" />
    </div>

    <div class="ml-auto min-w-[120px]">
        <label class="input-label">
            {{ esc_html__('Per page', 'wc-supplier-manager') }}
        </label>
        <select name="per_page" class="input-select">
            @foreach ([10, 20, 30, 50] as $pp)
                <option value="{{ esc_attr($pp) }}" {!! selected((int) $c['per_page'], $pp, false) !!}>
                    {{ esc_html($pp) }}/{{ esc_html__('page', 'wc-supplier-manager') }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <button type="submit" class="btn btn-outline">
            <x-lucide-filter />
            {{ esc_html__('Apply', 'woocommerce') }}
        </button>
    </div>
</form>