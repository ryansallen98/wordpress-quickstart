<div class="mb-6 flex items-start justify-between">
  <div>
    <h1 class="text-2xl font-bold">{!! $title !!}</h1>
  </div>
  <div class="flex gap-2">
    <a href="{{ $links['shop'] }}"
      class="btn btn-outline btn-sm">
      <x-lucide-chevron-left aria-hidden="true" />
      {!! __('Back to shop', 'wordpress-quickstart') !!}
    </a>
    <a href="{{ $links['account'] }}"
      class="btn btn-primary btn-sm">
      <x-lucide-pencil aria-hidden="true" />
      {!! __('Edit profile', 'wordpress-quickstart') !!}
    </a>
  </div>
</div>