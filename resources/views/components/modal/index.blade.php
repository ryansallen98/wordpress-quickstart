@props([
    'title' => null,       // string for the modal heading
    'modalId' => uniqid('modal-'), // unique id for aria attributes
])

<div
    x-data="modalComponent('{{ $modalId }}')"
    x-id="['modal-title']"
    class="inline"
>
    {{-- trigger (required): clicking this opens the modal and stores the trigger for focus return --}}
    <span x-ref="trigger">
        {{ $trigger }}
    </span>

    {{-- backdrop --}}
<div
    x-show="open"
    x-transition.opacity
    x-cloak
    class="fixed inset-0 z-40 bg-black/50"
    aria-hidden="true"
></div>

{{-- dialog container (covers screen, gets the click) --}}
<div
    x-show="open"
    x-transition
    x-cloak
    x-trap.noscroll.inert="open"
    role="dialog"
    :aria-labelledby="titleId"
    aria-modal="true"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    @keydown.escape.prevent.stop="close()"
    @click.self="close()"           {{-- ← add this --}}
>
    <div
        class="w-full max-w-lg rounded-lg bg-card p-6 shadow-xl outline-none"
        @click.stop                   {{-- keep clicks inside from bubbling --}}
        x-ref="dialog"
        tabindex="-1"
    >
            <div class="flex items-start justify-between gap-4">
                <h2
                    :id="titleId"
                    class="text-lg font-semibold leading-6"
                >
                    {{ $title ?? '' }}
                </h2>

                <button
                    type="button"
                    class="btn btn-outline btn-icon shrink-0"
                    @click="close()"
                >
                    <span class="sr-only">{!! __('Close modal', 'wordpress-quickstart') !!}</span>
                    <x-lucide-x aria-hidden="true" />
                </button>
            </div>

            {{-- body --}}
            <div class="mt-4">
                {{ $body }}
            </div>

            {{-- footer (optional) --}}
            @if (isset($footer))
            <div class="mt-6 flex items-center justify-end gap-2">
                {{ $footer }}
            </div>
            @endif
        </div>
    </div>
</div>