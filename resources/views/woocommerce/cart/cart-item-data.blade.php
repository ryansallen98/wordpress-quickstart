<dl class="variation flex flex-col text-xs text-muted-foreground mt-1">
    @foreach ($item_data as $data)
        <div class="flex gap-1">
            <dt class="{{ sanitize_html_class('variation-' . $data['key']) }} font-medium">
                {!! wp_kses_post($data['key']) !!}:
            </dt>
            <dd class="{{ sanitize_html_class('variation-' . $data['key']) }}">
                {!! wp_kses_post(wpautop($data['display'])) !!}
            </dd>
        </div>
    @endforeach
</dl>