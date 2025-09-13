<nav class="w-full">
    <ul class="flex items-end justify-start w-full">
        @foreach($mainMenu as $item)
        <li class="group {{ !empty($item['acf']['is_button']) ? 'ml-auto' : '' }}">
            <a class="btn {{ !empty($item['acf']['is_button']) ? 'btn-secondary' : 'btn-ghost' }}"
                href="{{ $item['url'] ?? '#' }}" @if(!empty($item['target'])) target="{{ $item['target'] }}" @endif>
                <span>{!! $item['label'] ?? $item['title'] !!}</span>
            </a>
            @if(!empty($item['children']))
            <div class="hidden absolute group-hover:block w-fit pt-2">
                <ul class="grid grid-flow-col grid-rows-3 gap-2 w-fit p-2 bg-card border rounded-md shadow-md animate-in fade-in zoom-in-90">
                    @foreach($item['children'] as $child)
                    <li
                        class="group/child overflow-hidden hover:bg-accent {{ !empty($child['acf']['is_featured']) ? 'row-span-3 featured min-h-80 rounded first:rounded-l-md last:rounded-r-md' : 'rounded-md' }}">
                        <a href="{{ $child['url'] ?? '#' }}"
                            class="flex flex-col p-2 relative max-w-[240px] h-full w-full no-underline! {{ !empty($child['acf']['is_featured']) ? 'p-4 min-h-50 text-white text-xl items-start justify-end' : '' }}">
                            @if(!empty($child['acf']['is_featured']) && !empty($child['acf']['image']['url']))
                            <img src="{{ $child['acf']['image']['url'] }}"
                                alt="{{ $child['acf']['image']['alt'] ?? '' }}"
                                class="absolute inset-0 h-full w-full object-cover transition-transform duration-200 group-hover/child:scale-105"
                                loading="lazy" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black to-transparent"></div>
                            @endif

                            <span class="relative z-10 font-bold">{!! $child['label'] ?? $child['title'] !!}</span>
                            @if(!empty($child['acf']['description']))
                            <span class="text-sm relative z-10">
                                {{ $child['acf']['description'] }}
                            </span>
                            @endif
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </li>
        @endforeach
    </ul>
</nav>

