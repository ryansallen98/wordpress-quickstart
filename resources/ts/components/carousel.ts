import EmblaCarousel, {
  EmblaCarouselType,
  EmblaOptionsType,
} from 'embla-carousel'
import Autoplay, { AutoplayType } from 'embla-carousel-autoplay'

type AlpineCtx = {
  $refs: { viewport: HTMLElement }
  $watch: (prop: string, cb: () => void, opts?: { defer?: boolean }) => void
  $nextTick: (cb: () => void) => void
  $el: HTMLElement
}

// Full options the plugin supports (mirror the docs)
type AutoplayOptions = {
  delay?: number
  jump?: boolean
  playOnInit?: boolean
  stopOnInteraction?: boolean
  stopOnMouseEnter?: boolean
  stopOnFocusIn?: boolean
  stopOnLastSnap?: boolean
  // If you need to target a different root than the Embla container:
  // rootNode?: (emblaRoot: HTMLElement) => HTMLElement
  rootNode?: (root: HTMLElement) => HTMLElement
}

type CarouselComponent = {
  opts: EmblaOptionsType & {
    axis?: 'x' | 'y'
    direction?: 'ltr' | 'rtl'
    autoplay?: boolean | AutoplayOptions
  }
  embla: EmblaCarouselType | null
  autoplayPlugin: AutoplayType | null

  selectedIndex: number
  slideCount: number
  canPrev: boolean
  canNext: boolean

  // orientation helpers
  isVertical: boolean

  // focus/keyboard
  hasFocusWithin: boolean
  _keyHandler?: (e: KeyboardEvent) => void

  _alive: boolean
  _resizeHandler?: () => void

  init(): void
  prev(): void
  next(): void
  to(i: number): void
  _onSelect(): void
  focusDot(i: number): void
  shouldIgnoreKey(e: KeyboardEvent): boolean

  // expose play/pause controls if you want a UI later
  play(jump?: boolean): void
  pause(): void
  resetAutoplay(): void

  destroy(): void
}

export default function carouselComponent(
  opts: EmblaOptionsType & {
    axis?: 'x' | 'y'
    direction?: 'ltr' | 'rtl'
    autoplay?: boolean | AutoplayOptions
  } = {},
): CarouselComponent {
  return {
    opts,
    embla: null,
    autoplayPlugin: null,

    selectedIndex: 0,
    slideCount: 0,
    canPrev: false,
    canNext: false,

    get isVertical() {
      return (this.opts.axis ?? 'x') === 'y'
    },

    hasFocusWithin: false,
    _keyHandler: undefined,

    _alive: true,
    _resizeHandler: undefined,

    init() {
      const ctx = this as unknown as AlpineCtx
      const viewport = ctx.$refs.viewport as HTMLElement

      // Build plugin list (conditionally include Autoplay with all options)
      const plugins: any[] = []
      if (this.opts.autoplay) {
        const defaults: AutoplayOptions = {
          delay: 4000,
          jump: false,
          playOnInit: true,
          stopOnInteraction: true,
          stopOnMouseEnter: false,
          stopOnFocusIn: true,
          stopOnLastSnap: false,
          // rootNode: (root) => root, // default
        }
        const user = typeof this.opts.autoplay === 'object' ? this.opts.autoplay : {}
        const config: AutoplayOptions = { ...defaults, ...user }
        this.autoplayPlugin = Autoplay(config)
        plugins.push(this.autoplayPlugin)
      }

      // Init Embla
      this.embla = EmblaCarousel(
        viewport,
        { ...this.opts, axis: this.opts.axis ?? 'x', direction: this.opts.direction ?? 'ltr' },
        plugins,
      )

      // State sync
      this.slideCount = this.embla.slideNodes().length
      this._onSelect()
      this.embla.on('select', () => this._onSelect())
      this.embla.on('reInit', () => this._onSelect())

      // Resize
      this._resizeHandler = () => this.embla && this.embla.reInit({
        ...this.opts,
        axis: this.opts.axis ?? 'x',
        direction: this.opts.direction ?? 'ltr',
      })
      window.addEventListener('resize', this._resizeHandler)

      // Window-level key handling (orientation-aware)
      this._keyHandler = (e: KeyboardEvent) => {
        if (!this.hasFocusWithin) return
        if (this.shouldIgnoreKey(e)) return

        if (!this.isVertical) {
          if (e.key === 'ArrowLeft') { e.preventDefault(); this.prev(); return }
          if (e.key === 'ArrowRight') { e.preventDefault(); this.next(); return }
        } else {
          if (e.key === 'ArrowUp') { e.preventDefault(); this.prev(); return }
          if (e.key === 'ArrowDown') { e.preventDefault(); this.next(); return }
        }
        if (e.key === 'Home') { e.preventDefault(); this.to(0); return }
        if (e.key === 'End') { e.preventDefault(); this.to(Math.max(0, this.slideCount - 1)); return }
      }
      window.addEventListener('keydown', this._keyHandler)

      // Alpine cleanup hook (kept)
      queueMicrotask(() => ctx.$watch('_alive', () => {}, { defer: true }))
    },

    prev() { this.embla?.scrollPrev() },
    next() { this.embla?.scrollNext() },
    to(i: number) { this.embla?.scrollTo(i) },

    _onSelect() {
      if (!this.embla) return
      this.selectedIndex = this.embla.selectedScrollSnap()
      this.canPrev = this.embla.canScrollPrev()
      this.canNext = this.embla.canScrollNext()
    },

    shouldIgnoreKey(e: KeyboardEvent) {
      if ((e as any).repeat) return true
      if (e.altKey || e.ctrlKey || e.metaKey || e.shiftKey) return true
      const t = e.target as HTMLElement | null
      if (!t) return false
      if (t.closest('input, textarea, select, [contenteditable], button, a[href], [role=button], [role=tab], [role=menu], [role=combobox]')) {
        return true
      }
      return false
    },

    focusDot(i: number) {
      const ctx = this as unknown as AlpineCtx
      const target = Math.max(0, Math.min(i, this.slideCount - 1))
      this.to(target)
      ctx.$nextTick(() => {
        const btn = ctx.$el.querySelector<HTMLButtonElement>(`[data-dot-index="${target}"]`)
        btn?.focus()
      })
    },

    // Public controls for a pause/play UI
    play(jump?: boolean) { this.autoplayPlugin?.play(jump) },
    pause() { this.autoplayPlugin?.stop() },
    resetAutoplay() { this.autoplayPlugin?.reset() },

    destroy() {
      if (this._resizeHandler) window.removeEventListener('resize', this._resizeHandler)
      if (this._keyHandler) window.removeEventListener('keydown', this._keyHandler)
      if (this.embla) this.embla.destroy()
      this.embla = null
      this.autoplayPlugin = null
    },
  }
}