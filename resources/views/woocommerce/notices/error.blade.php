@if (! empty($notices))
  <x-alert variant="destructive" class="my-2">
    <x-heroicon-o-shield-exclamation class="shrink-0 stroke-2" />

    <x-alert.title>
      {{ __('There was a problem', 'wordpress-quickstart') }}
    </x-alert.title>

    <x-alert.description>
      <ul role="list" class="mt-1 flex flex-col gap-1 text-xs!">
        @foreach ($notices as $notice)
          <li {!! wc_get_notice_data_attr($notice) !!}>
            {!! wc_kses_notice($notice['notice']) !!}
          </li>
        @endforeach
      </ul>
    </x-alert.description>
  </x-alert>
@endif
