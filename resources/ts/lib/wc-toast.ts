function flushToastsFromFragment() {
  const el = document.getElementById('wc-toasts-fragment') as HTMLElement | null;
  const json = el?.getAttribute('data-toasts');
  if (!json) return;
  try {
    const arr = JSON.parse(json);
    if (Array.isArray(arr)) arr.forEach((t) => window.toast?.(t));
  } catch {}
  el?.setAttribute('data-toasts', '');
}

// Full page bootstraps (the footer script injects __BOOTSTRAP_TOASTS__ too,
// but this handles fragment container if present on load)
document.addEventListener('DOMContentLoaded', flushToastsFromFragment);

// Woo’s jQuery fragment events (preferred)
if (typeof (window as any).jQuery !== 'undefined') {
  const $ = (window as any).jQuery;
  $(document.body).on('wc_fragments_loaded wc_fragments_refreshed', flushToastsFromFragment);
}