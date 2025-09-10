<div class="relative flex w-full flex-col justify-between lg:flex-row">
  <div
    class="lg:bg-sidebar order-first flex flex-1 flex-col justify-between gap-4 pb-0 lg:sticky lg:top-0 lg:h-[100dvh] lg:max-w-[560px] lg:overflow-y-auto lg:border-r"
  >
    <div class="p-4 lg:p-16">
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

    {{-- Legal Menu --}}
    <nav
      class="hidden p-2 px-4 lg:flex"
      aria-label="{{ __('Legal', 'sage') }}"
    >
      <ul class="flex items-center gap-3">
        @forelse ($menu as $item)
          <li>
            <a
              href="{{ $item->url }}"
              @if(!empty($item->target)) target="{{ $item->target }}" @endif
              @if(!empty($item->rel))    rel="{{ $item->rel }}"       @endif
              class="text-muted-foreground hover:text-foreground {{ $item->active ?? false ? 'text-foreground' : '' }} text-sm no-underline!"
              aria-current="{{ $item->active ?? false ? 'page' : 'false' }}"
            >
              {{ $item->label ?? $item->title }}
            </a>
          </li>
        @empty
          <li class="text-muted-foreground text-sm">
            {{ __('Assign a menu to “checkout_footer”.', 'sage') }}
          </li>
        @endforelse
      </ul>
    </nav>
  </div>

  <div class="flex flex-1 flex-col p-4 pt-0 lg:pt-4">
    <div class="hidden lg:block">
      <a class="btn btn-ghost" href="{{ $returnUrl }}">
        <x-lucide-chevron-left aria-hidden="true" />
        {{ $returnText }}
      </a>
    </div>

    <div
      class="mx-auto w-full flex-1 lg:max-w-[720px] lg:min-w-[560px] lg:p-12"
    >
      {!! $right ?? '' !!}
      @empty($right)
        @stack('right')
      @endempty
    </div>
  </div>

  {{-- Legal Menu --}}
  <nav class="p-2 px-4 flex lg:hidden" aria-label="{{ __('Legal', 'sage') }}">
    <ul class="flex items-center gap-3">
      @forelse ($menu as $item)
        <li>
          <a
            href="{{ $item->url }}"
            @if(!empty($item->target)) target="{{ $item->target }}" @endif
            @if(!empty($item->rel))    rel="{{ $item->rel }}"       @endif
            class="text-muted-foreground hover:text-foreground {{ $item->active ?? false ? 'text-foreground' : '' }} text-sm no-underline!"
            aria-current="{{ $item->active ?? false ? 'page' : 'false' }}"
          >
            {{ $item->label ?? $item->title }}
          </a>
        </li>
      @empty
        <li class="text-muted-foreground text-sm">
          {{ __('Assign a menu to “checkout_footer”.', 'sage') }}
        </li>
      @endforelse
    </ul>
  </nav>
</div>
