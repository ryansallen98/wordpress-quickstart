@php
    $is_preview = (bool) data_get($context, '_meta.is_preview', false);
@endphp

<div class="relative">
    <x-carousel @class([
        'overflow-hidden',
        'mb-12 shadow-xl rounded-lg' => !$is_preview,
    ])>
        <x-carousel.container>
            @foreach($context['slides'] as $slide)
                <x-carousel.item>
                    <div class="px-24 bg-black min-h-140 flex flex-col justify-center relative"
                        style="background: url('{{ $slide['image']['url'] }}'); background-size: cover; background-position: center;">
                        <div class="absolute inset-0 bg-gradient-to-r from-black to-transparent"></div>
                        <div class="max-w-xl relative">
                            <h1 class="text-6xl font-bold mb-4 text-white">{{ $slide['title'] }}</h1>
                            <p class="text-xl text-white">{{ $slide['description'] }}</p>

                            @if($slide['has_button'] === true)
                                <a href="{{ $slide['button_link']['url'] }}"
                                    class="btn btn-{{ $slide['button_type'] }} btn-lg mt-8 text-xl h-auto px-8 py-3">
                                    {{ $slide['button_link']['title'] }}
                                </a>
                            @endif
                        </div>
                    </div>
                </x-carousel.item>
            @endforeach
        </x-carousel.container>

        <x-carousel.prev class="btn btn-outline btn-icon" />
        <x-carousel.next class="btn btn-outline btn-icon" />
        <x-carousel.dots />
    </x-carousel>
</div>





@push('afc_debug')
    {!! $context['_meta']['slug'] !!}
{!! json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}@endpush