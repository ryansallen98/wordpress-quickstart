@php
    $ajax_url = esc_url(admin_url('admin-ajax.php'));
    $nonce = wp_create_nonce('wcps');
@endphp

@if (class_exists('WooCommerce'))
    <div x-data="wcpsSearch()" x-init="init()" x-on:resize.window="
        isDesktop = window.matchMedia('(min-width:1024px)').matches;
        if (window.innerWidth >= 1024) {
          document.documentElement.classList.remove('overflow-hidden');
          document.body.classList.remove('overflow-hidden');
        } else if (open) {
          document.documentElement.classList.add('overflow-hidden');
          document.body.classList.add('overflow-hidden');
        }
        if (!isDesktop) popperOpen = false;
      " class="relative">
        <button type="button" class="relative btn btn-ghost btn-icon flex lg:hidden" @click="open = !open"
            :aria-expanded="open.toString()" aria-controls="wcps-form">
            <x-heroicon-s-magnifying-glass class="size-5" />
            <span class="sr-only">{{ __('Search', 'wordpress-quickstart') }}</span>
        </button>

        <form
            class="lg:relative hidden data-[open='true']:flex lg:block lg:data-[open='true']:block fixed left-0 top-0 h-[100dvh] w-full bg-card z-10 items-start justify-start flex-col lg:bg-transparent lg:h-auto"
            :data-open="open" id="wcps-form" hx-get="{{ $ajax_url }}" hx-target="#wcps-results" hx-swap="innerHTML"
            hx-trigger="keyup[
          event.key && !['ArrowDown','ArrowUp','Home','End','Enter','Escape','Tab'].includes(event.key)
          && event.target.value.length >= 3
        ] from:#wcps-search delay:300ms">
            <label for="wcps-search" class="sr-only">
                {!! __('Search products…', 'wordpress-quickstart') !!}
            </label>

            <div class="flex w-full lg:w-auto p-4 lg:p-0">
                <div class="relative flex items-center w-full">
                    <x-lucide-search class="size-4 absolute my-auto ml-3" aria-hidden="true" />

                    <input id="wcps-search" type="search" name="q"
                        class="input-text px-8.5! min-w-xs! rounded-r-none! lg:rounded-r-md!"
                        placeholder="{!! __('Search products…', 'wordpress-quickstart') !!}" autocomplete="off"
                        inputmode="search" role="combobox" aria-autocomplete="list" aria-haspopup="listbox"
                        :aria-expanded="(isDesktop && popperOpen).toString()" aria-controls="wcps-results"
                        :aria-activedescendant="activeId ? activeId : null"
                        @focus="isFocused = true; if (isDesktop) openPopper()" @blur="isFocused = false"
                        @click="if (isDesktop) openPopper()" @input="if (isDesktop) { openPopper(); activeIndex = -1; }"
                        @keydown.arrow-down.prevent="openAndMove(1)" @keydown.arrow-up.prevent="openAndMove(-1)"
                        @keydown.home.prevent="setActiveByIndex(0)"
                        @keydown.end.prevent="setActiveByIndex(options.length - 1)"
                        @keydown.enter.prevent="activateActive()"
                        hx-on:keyup="if (this.value.length < 3) { document.getElementById('wcps-results').innerHTML=''; }" />

                    <div class="absolute my-auto right-0 mr-3 text-muted-foreground htmx-indicator">
                        <x-lucide-loader-circle class="size-4 animate-spin" aria-hidden="true" />
                        <span class="sr-only">{!! __('Loading…', 'wordpress-quickstart') !!}</span>
                    </div>

                    <kbd x-show="!isFocused"
                        class="bg-card text-card-foreground text-xs border py-0.5 px-1 rounded hidden items-center gap-1 absolute right-3 lg:flex">
                        <template x-if="isMac"><span aria-hidden="true">⌘</span></template>
                        <template x-if="!isMac"><span aria-hidden="true">Ctrl</span></template>
                        <span aria-hidden="true">K</span>
                    </kbd>
                </div>

                <button type="button" class="relative btn btn-outline btn-icon flex lg:hidden rounded-l-none! border-l-0!"
                    @click="open = !open" :aria-expanded="open.toString()" aria-controls="wcps-form">
                    <x-lucide-x class="size-5" />
                    <span class="sr-only">{{ __('Close', 'wordpress-quickstart') }}</span>
                </button>
            </div>

            <div id="wcps-hidden" class="hidden">
                <input type="hidden" name="action" value="wc_product_search">
                <input type="hidden" name="_ajax_nonce" value="{{ esc_attr($nonce) }}">
            </div>

            <div id="wcps-popover-wrap" :data-popper="isDesktop && popperOpen ? 'open' : 'closed'"
                :class="{ 'lg:block': isDesktop && popperOpen, 'lg:hidden': isDesktop && !popperOpen }"
                class="lg:absolute lg:mt-2 w-full lg:data-[popper='open']:block lg:data-[popper='closed']:hidden animate-in fade-in zoom-in-90 top-[100%] overflow-hidden overflow-y-auto lg:shadow-md lg:rounded-md">
                <div id="wcps-popover"
                    class="z-50 w-full overflow-hidden overflow-y-auto lg:border lg:bg-popover text-popover-foreground lg:max-h-[320px] lg:p-2 lg:pt-2 hidden has-[ul]:block has-[p]:block p-4 pt-0"
                    aria-live="polite">
                    <div id="wcps-results" role="listbox" aria-label="@lang('Product results')" aria-busy="false">
                        {{-- HTMX will swap the server HTML fragment in here --}}
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('wcpsSearch', () => ({
                    open: false,
                    popperOpen: false,
                    isDesktop: window.matchMedia('(min-width:1024px)').matches,

                    // a11y/selection
                    options: [],
                    activeIndex: -1,
                    get activeId() { return (this.options[this.activeIndex]?.id) || null; },

                    // KBD + focus state
                    isFocused: false,
                    isMac: (() => {
                        const plat = (navigator.userAgentData && navigator.userAgentData.platform) || navigator.platform || '';
                        return /Mac/i.test(plat);
                    })(),

                    resultsObserver: null,

                    init() {
                        // scroll lock
                        const lock = () => { document.documentElement.classList.add('overflow-hidden'); document.body.classList.add('overflow-hidden'); };
                        const unlock = () => { document.documentElement.classList.remove('overflow-hidden'); document.body.classList.remove('overflow-hidden'); };
                        this.$watch('open', v => { if (window.innerWidth < 1024 && v) { lock(); } else { unlock(); } });

                        // observe results (re-index)
                        const results = document.getElementById('wcps-results');
                        if (results) {
                            this.resultsObserver = new MutationObserver(() => {
                                this.indexOptions();
                                if (this.isDesktop && document.activeElement?.id === 'wcps-search') this.openPopper();
                            });
                            this.resultsObserver.observe(results, { childList: true, subtree: true });
                        }

                        // also listen to htmx swaps
                        document.addEventListener('htmx:afterSwap', (e) => {
                            if (e.target && e.target.id === 'wcps-results') {
                                this.indexOptions();
                                if (this.isDesktop && document.activeElement?.id === 'wcps-search') this.openPopper();
                            }
                        });

                        // outside click (desktop)
                        const form = document.getElementById('wcps-form');
                        const outside = (evt) => {
                            if (!this.isDesktop) return;
                            if (form && !form.contains(evt.target)) this.closePopper();
                        };
                        document.addEventListener('mousedown', outside, true);
                        document.addEventListener('touchstart', outside, { capture: true, passive: true });

                        // Escape to close
                        document.addEventListener('keydown', (e) => {
                            if (this.isDesktop && e.key === 'Escape') this.closePopper();
                        });

                        // Hover sets active
                        if (results) {
                            results.addEventListener('mousemove', (e) => {
                                const opt = e.target.closest('[role="option"]');
                                if (!opt) return;
                                const idx = this.options.indexOf(opt);
                                if (idx > -1) this.setActiveByIndex(idx, { scroll: false });
                            });
                        }

                        // Global Command/Ctrl+K: focus search + open UI
                        document.addEventListener('keydown', (e) => {
                            const isK = (e.key || '').toLowerCase() === 'k';
                            if (!isK) return;

                            const cmdPressed = this.isMac ? e.metaKey : e.ctrlKey;
                            if (!cmdPressed) return;

                            // Avoid interfering inside editable fields other than the search itself
                            const tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : '';
                            const editable = e.target && (e.target.isContentEditable || tag === 'textarea' || (tag === 'input' && e.target.type !== 'search' && e.target.type !== 'text'));
                            if (editable) return;

                            e.preventDefault();
                            const input = document.getElementById('wcps-search');
                            if (input) {
                                if (this.isDesktop) {
                                    this.openPopper();
                                } else {
                                    this.open = true; // open mobile overlay
                                }
                                // focus + place caret at end
                                input.focus();
                                const v = input.value;
                                input.setSelectionRange(v.length, v.length);
                            }
                        });

                        // initial index
                        this.indexOptions();
                    },

                    // visibility
                    openPopper() { this.popperOpen = true; },
                    closePopper() { this.popperOpen = false; this.clearActive(); },

                    // options
                    indexOptions() {
                        const lb = document.getElementById('wcps-results');
                        this.options = lb ? Array.from(lb.querySelectorAll('[role="option"]')) : [];
                        this.options.forEach((el, i) => {
                            if (!el.id) el.id = `wcps-option-${i}`;
                            if (!el.hasAttribute('tabindex')) el.setAttribute('tabindex', '-1');
                            el.setAttribute('aria-selected', (i === this.activeIndex) ? 'true' : 'false');
                        });
                        if (this.activeIndex >= this.options.length) this.activeIndex = -1;
                    },

                    clearActive() {
                        if (this.activeIndex > -1 && this.options[this.activeIndex]) {
                            this.options[this.activeIndex].setAttribute('aria-selected', 'false');
                        }
                        this.activeIndex = -1;
                    },

                    setActiveByIndex(i, opts = { scroll: true }) {
                        if (!this.options.length) return;
                        i = Math.max(0, Math.min(i, this.options.length - 1));
                        if (this.activeIndex === i) return;
                        if (this.activeIndex > -1 && this.options[this.activeIndex]) {
                            this.options[this.activeIndex].setAttribute('aria-selected', 'false');
                        }
                        this.activeIndex = i;
                        const el = this.options[i];
                        el.setAttribute('aria-selected', 'true');
                        if (opts.scroll !== false) this.scrollIntoView(el);
                    },

                    openAndMove(delta) {
                        if (this.isDesktop && !this.popperOpen) this.openPopper();
                        if (!this.options.length) this.indexOptions();
                        if (!this.options.length) return;

                        if (this.activeIndex === -1) {
                            this.setActiveByIndex(delta > 0 ? 0 : this.options.length - 1);
                        } else {
                            let next = this.activeIndex + delta;
                            if (next < 0) next = this.options.length - 1;
                            if (next >= this.options.length) next = 0;
                            this.setActiveByIndex(next);
                        }
                    },

                    activateActive() {
                        if (this.activeIndex === -1) return;
                        const el = this.options[this.activeIndex];
                        if (el.tagName === 'A' && el.href) {
                            el.click();
                        } else {
                            el.dispatchEvent(new MouseEvent('click', { bubbles: true }));
                        }
                    },

                    scrollIntoView(el) {
                        const container = document.getElementById('wcps-popover');
                        if (!container || !el) return;
                        const cTop = container.scrollTop;
                        const cBottom = cTop + container.clientHeight;
                        const eTop = el.offsetTop;
                        const eBottom = eTop + el.offsetHeight;
                        if (eTop < cTop) container.scrollTop = eTop;
                        else if (eBottom > cBottom) container.scrollTop = eBottom - container.clientHeight;
                    },
                }));
            });
        </script>
    @endpush
@endif