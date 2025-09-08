@php($list = is_array($notices ?? null) ? $notices : [])

@foreach ($list as $notice)
  <x-alert class="my-2" variant="success">
    <x-heroicon-o-check-circle class="stroke-2" />
    <x-alert.title>{{ __('Success', 'wordpress-quickstart') }}</x-alert.title>
    <x-alert.description>{!! wc_kses_notice($notice['notice'] ?? '') !!}</x-alert.description>
  </x-alert>
@endforeach