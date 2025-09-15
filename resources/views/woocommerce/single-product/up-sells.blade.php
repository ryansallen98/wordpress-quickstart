@if ($show_modal)
    @push('modals')
        <div x-data="{
                            open: true,
                            prev: null,
                            lock(v) {
                              const root = document.documentElement
                              const body = document.body
                              root.classList.toggle('overflow-hidden', v)
                              body.classList.toggle('overflow-hidden', v)
                            },
                            close() {
                              this.open = false
                              this.lock(false)
                              this.prev && this.prev.focus({ preventScroll: true })
                            }
                          }" x-init="
                            prev = document.activeElement;
                            lock(true); // ✅ lock immediately on first render
                            $watch('open', v => lock(v)); // keep in sync
                            $nextTick(() => { $el.querySelector('[data-autofocus]')?.focus({ preventScroll: true }) });
                          ">
            <template x-teleport="body">
                <div x-show="open" x-transition.opacity class="fixed inset-0 z-[1000] flex items-center justify-center"
                    role="dialog" aria-modal="true">
                    <div class="absolute inset-0 bg-black/50" @click="close()"></div>

                    <div x-show="open" x-transition
                        class="relative z-10 w-full max-w-5xl md:rounded-lg border bg-card text-card-foreground shadow-lg outline-none max-h-[100dvh] overflow-y-auto"
                        @keydown.escape.window="close()" tabindex="-1">
                        {{-- Header --}}
                        <div class="flex items-start justify-between gap-4 p-4 border-b sticky top-0 bg-card z-1">
                            <div>
                                <h2 class="text-2xl font-semibold tracking-tight">
                                    {!! $smart_heading !!}
                                </h2>
                                @if ($lead)
                                    <p class="mt-1 text-sm text-muted-foreground">
                                        {{ $lead }}
                                    </p>
                                @endif
                            </div>
                            <button type="button" class="btn btn-outline btn-icon" @click="close()" data-autofocus>
                                <span class="sr-only">{{ __('Close', 'woocommerce') }}</span>
                                <x-lucide-x />
                            </button>
                        </div>

                        {{-- Content --}}
                        <div class="p-4">
                            <div class="grid gap-6 items-stretch justify-center
                 [grid-template-columns:var(--cols)]
                 sm:[grid-template-columns:var(--cols-sm)]
                 md:[grid-template-columns:var(--cols-md)]
                 lg:[grid-template-columns:var(--cols-lg)]
                 xl:[grid-template-columns:var(--cols-xl)]" style="
            --cols:    repeat(2, minmax(0, 1fr));
            --cols-sm: repeat({{ min($upsell_count, 2) }}, minmax(0, 1fr));
            --cols-md: repeat({{ min($upsell_count, 3) }}, minmax(0, 1fr));
            --cols-lg: repeat({{ min($upsell_count, 4) }}, minmax(0, 1fr));
            --cols-xl: repeat({{ min($upsell_count, 4) }}, minmax(0, 1fr));
          ">
                                @foreach ($upsells as $upsell)
                                    @php
                                        $post_object = get_post($upsell->get_id());
                                        setup_postdata($GLOBALS['post'] = $post_object);
                                      @endphp

                                    {!! wc_get_template_part('content', 'product') !!}
                                @endforeach
                            </div>
                        </div>

                        {{-- Footer CTAs --}}
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 p-4 border-t sticky bottom-0 bg-card z-1">
                            <button type="button" class="btn btn-link btn-lg" @click="close()">
                                {{ __('Continue shopping', 'woocommerce') }}
                            </button>
                            <a href="{{ esc_url($cart_url) }}" class="btn btn-outline btn-lg">
                                <x-heroicon-s-shopping-bag aria-hidden="true" />
                                {{ __('View cart', 'woocommerce') }}
                            </a>
                            <a href="{{ esc_url($checkout_url) }}" class="btn btn-primary btn-lg">
                                <x-heroicon-s-credit-card aria-hidden="true" />
                                {{ __('Checkout', 'woocommerce') }}
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        @php wp_reset_postdata(); @endphp
    @endpush
@endif