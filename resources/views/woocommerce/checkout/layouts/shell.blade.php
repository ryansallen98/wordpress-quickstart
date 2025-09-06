<div class="relative flex w-full flex-col justify-between lg:flex-row">
  <div
    class="lg:bg-accent order-first flex-1 p-4 pb-0 lg:sticky lg:top-0 lg:h-[100dvh] lg:max-w-[560px] lg:overflow-y-auto lg:border-r lg:p-16"
  >
    <div class="block lg:hidden">
      <a class="btn btn-ghost mb-4 px-0!" href="{{ $returnUrl }}">
        <x-lucide-chevron-left aria-hidden="true" />
        {{ $returnText }}
      </a>
    </div>

    {{-- Prefer explicit vars, else fallback to stack --}}
    {!! $left ?? '' !!}
    @empty($left)
      @stack('left')
    @endempty
  </div>

  <div class="flex flex-1 flex-col p-4 pt-0 lg:pt-4">
    <div class="hidden lg:block">
      <a class="btn btn-ghost" href="{{ $returnUrl }}">
        <x-lucide-chevron-left aria-hidden="true" />
        {{ $returnText }}
      </a>
    </div>

    <div class="mx-auto flex-1 lg:max-w-[720px] lg:min-w-[560px] w-full --lg:p-12">
      {!! $right ?? '' !!}
      @empty($right)
        @stack('right')
      @endempty
    </div>
  </div>
</div>
