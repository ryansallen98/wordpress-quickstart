<form method="post" class="p-4 border-b bg-accent/20"
    action="{{ esc_url(wc_get_account_endpoint_url(\WCSM\Accounts\SupplierOrdersEndpoint::ENDPOINT)) }}">
    @php wp_nonce_field('wcsm_supplier_orders'); @endphp
    <input type="hidden" name="wcsm_so_action" value="update" />
    <input type="hidden" name="order_id" value="{{ esc_attr($order_id) }}" />

    <p>
        <label class="flex flex-col gap-2 mb-2">
            <span class="input-label">{{ esc_html__('Your status', 'wc-supplier-manager') }}</span>
            <select name="wcsm_status" class="input-select">
                @foreach ($status_opts as $k => $label)
                    <option value="{{ esc_attr($k) }}" {!! selected($mine['status'] ?? 'pending', $k, false) !!}>
                        {{ esc_html($label) }}
                    </option>
                @endforeach
            </select>
        </label>
    </p>

    <p class="flex flex-row gap-2">
        <label class="flex flex-col gap-2">
            <span class="input-label">{{ esc_html__('Carrier', 'wc-supplier-manager') }}</span>
            <input type="text" name="wcsm_carrier" value="{{ esc_attr($mine['tracking']['carrier'] ?? '') }}" class="input-text" />
        </label>
        <label class="flex flex-col gap-2">
            <span class="input-label">{{ esc_html__('Tracking number', 'wc-supplier-manager') }}</span>
            <input type="text" name="wcsm_tracking" value="{{ esc_attr($mine['tracking']['number'] ?? '') }}" class="input-text" />
        </label>
        <label class="flex-1 flex flex-col gap-2">
            <span class="input-label">{{ esc_html__('Tracking URL', 'wc-supplier-manager') }}</span>
            <input type="url" name="wcsm_url" value="{{ esc_url($mine['tracking']['url'] ?? '') }}" class="input-text" />
        </label>
    </p>

    <p class="mt-2 mb-4">
        <label class="flex flex-col gap-2">
            <span class="input-label">
                {{ esc_html__('Notes (optional, required if rejecting)', 'wc-supplier-manager') }}
            </span>
            <textarea name="wcsm_notes" rows="3"
                class="input-text">{{ esc_textarea($mine['tracking']['notes'] ?? '') }}</textarea>
        </label>
    </p>

    <p>
        <button type="submit" class="button button-primary">
            {{ esc_html__('Save', 'wc-supplier-manager') }}
        </button>
    </p>
</form>