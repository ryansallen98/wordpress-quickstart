// ---- Types ----
type ToastType = 'success' | 'error' | 'info';

export interface ToastAction {
  label: string;
  handler?: () => void;
  href?: string;
}

export interface ToastInput {
  type?: ToastType;
  title?: string;
  text?: string;
  /** If omitted, the toast will NOT auto-dismiss. Set ms to enable auto-dismiss. */
  timeout?: number; // ms, optional
  actions?: ToastAction[];
  onClose?: () => void;
}

interface ToastItemRequired {
  id: number;
  show: boolean;
  /** If timeout is null (no auto-dismiss), remaining is ignored */
  remaining: number;
  started: number;
  progress: number;
  _timer: number | null;
  _raf: number | null;
  _paused: boolean;
}

export type ToastItem = {
  type: ToastType;
  title: string;
  text: string;
  /** null = no auto-dismiss */
  timeout: number | null;
  actions: ToastAction[];
  onClose: (() => void) | null;
} & ToastItemRequired;

// Extend Window interface to include __BOOTSTRAP_TOASTS__
declare global {
  interface Window {
    __BOOTSTRAP_TOASTS__?: ToastInput[];
    __TOASTS_WIRED__?: boolean;
    toast?: (opts: string | ToastInput) => void;
    toastCenter?: () => any;
  }
}
// ...window declaration stays the same...

(() => {
  if (typeof window === 'undefined') return;
  if (window.__TOASTS_WIRED__) return;
  window.__TOASTS_WIRED__ = true;

  const _uid = (() => { let i = 1; return () => i++; })();

  window.toast = function toast(opts: string | ToastInput) {
    const detail: ToastInput = typeof opts === 'string' ? { text: opts } : (opts || {});
    window.dispatchEvent(new CustomEvent<ToastInput>('toast:add', { detail }));
  };

  window.toastCenter = function toastCenter() {
    return {
      toasts: [] as ToastItem[],
      /** no longer used as a fallback; keeping if you want a global default */
      defaultTimeout: 5000,
      max: 5,

      init() {
        window.addEventListener('toast:add', (e: Event) => {
          const detail = (e as CustomEvent<ToastInput>).detail || {};
          this.add(detail);
        });
        window.addEventListener('toast:clear', () => this.clear());

        if (Array.isArray(window.__BOOTSTRAP_TOASTS__)) {
          window.__BOOTSTRAP_TOASTS__.forEach((t) => this.add(t));
          window.__BOOTSTRAP_TOASTS__ = [];
        }
      },

      // typeClasses(type: ToastType) {
      //   return {
      //     'border-l-4 border-success': type === 'success',
      //     'border-l-4 border-destructive': type === 'error',
      //     'border-l-4 border-info': type === 'info',
      //   };
      // },
      barClasses(type: ToastType) {
        return {
          'bg-success': type === 'success',
          'bg-destructive': type === 'error',
          'bg-info': type === 'info',
        };
      },

      add(input: ToastInput) {
        const text = String(input.text ?? '').trim();
        if (!text) return;

        // If timeout is a finite non-negative number, use it; otherwise null = no auto-dismiss
        const hasFiniteTimeout =
          typeof input.timeout === 'number' && Number.isFinite(input.timeout) && input.timeout >= 0;
        const timeout: number | null = hasFiniteTimeout ? input.timeout! : null;

        const t: ToastItem = {
          id: _uid(),
          type: input.type ?? 'info',
          title: input.title ?? '',
          text,
          show: true,
          timeout,
          remaining: hasFiniteTimeout ? (input.timeout as number) : 0,
          started: Date.now(),
          progress: 0,
          actions: Array.isArray(input.actions) ? input.actions : [],
          onClose: typeof input.onClose === 'function' ? input.onClose : null,
          _timer: null,
          _raf: null,
          _paused: false,
        };

        // enforce max
        while (this.toasts.length >= this.max) {
          this._forceDismiss(this.toasts[0].id);
        }

        this.toasts.push(t);
        // only start timer if we actually have one
        if (t.timeout !== null) this._startTimer(t.id);
      },

      pause(id: number) {
        const t = this._get(id);
        if (!t || t._paused || t.timeout === null) return; // no-op if no timeout
        t._paused = true;
        const elapsed = Date.now() - t.started;
        t.remaining = Math.max(0, t.remaining - elapsed);
        if (t._timer !== null) window.clearTimeout(t._timer);
        if (t._raf !== null) window.cancelAnimationFrame(t._raf);
        this._updateProgress(t, 0);
      },

      resume(id: number) {
        const t = this._get(id);
        if (!t || !t._paused || t.timeout === null) return; // no-op if no timeout
        t._paused = false;
        this._startTimer(id);
      },

      dismiss(id: number) {
        const t = this._get(id);
        if (!t) return;
        t.show = false;
        if (t._timer !== null) window.clearTimeout(t._timer);
        if (t._raf !== null) window.cancelAnimationFrame(t._raf);
        window.setTimeout(() => {
          this._remove(id);
          try { t.onClose?.(); } catch {}
        }, 160);
      },

      clear() {
        [...this.toasts].forEach((t) => this.dismiss(t.id));
      },

      handleAction(id: number, action: ToastAction) {
        if (typeof action?.handler === 'function') {
          try { action.handler(); } catch {}
        } else if (typeof action?.href === 'string') {
          window.location.assign(action.href);
        }
        this.dismiss(id);
      },

      _get(id: number): ToastItem | undefined { return this.toasts.find((t: ToastItem) => t.id === id); },
      _remove(id: number): void { this.toasts = this.toasts.filter((t: ToastItem) => t.id !== id); },
      _forceDismiss(id: number) {
        const t = this._get(id);
        if (!t) return;
        if (t._timer !== null) window.clearTimeout(t._timer);
        if (t._raf !== null) window.cancelAnimationFrame(t._raf);
        this._remove(id);
        try { t.onClose?.(); } catch {}
      },

      _startTimer(id: number) {
        const t = this._get(id);
        if (!t || t.timeout === null) return; // nothing to do if no timeout

        t.started = Date.now();

        const tick = () => {
          const current = this._get(id);
          if (!current || current._paused || current.timeout === null) return;

          const elapsed = Date.now() - current.started;
          const left = Math.max(0, current.remaining - elapsed);
          const total = Math.max(1, current.timeout); // safe guard
          this._updateProgress(current, 100 - Math.round((left / total) * 100));

          if (left <= 0) {
            this.dismiss(id);
            return;
          }
          current._raf = window.requestAnimationFrame(tick);
        };

        t._timer = window.setTimeout(() => this.dismiss(id), t.remaining);
        t._raf = window.requestAnimationFrame(tick);
      },

      _updateProgress(t: ToastItem, pct: number) {
        if (!Number.isFinite(pct) || t.timeout === null) {
          t.progress = 0; // hide bar for sticky toasts
          return;
        }
        t.progress = Math.max(0, Math.min(100, pct));
      },
    };
  };
})();