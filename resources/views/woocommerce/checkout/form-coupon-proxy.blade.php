{{-- /**
 * Proxy coupon form for WooCommerce Checkout
 * ------------------------------------------------------------
 * Keeps Stripe/Woo expecting the coupon form in place, while
 * providing a custom UI. Submitting this proxy will NOT submit
 * the main checkout/payment form.
 */ --}}

@if (! wc_coupons_enabled())
  @php return; @endphp
@endif

<form
  id="proxy-coupon-form"
  method="post"
  aria-labelledby="coupon-legend-proxy"
  aria-describedby="coupon-hint-proxy"
  novalidate
>
  <fieldset class="m-0 border-0 p-0">
    <legend id="coupon-legend-proxy" class="mb-2 font-bold">
      {{ esc_html__('Have a coupon?', 'woocommerce') }}
    </legend>

    <p id="coupon-hint-proxy" class="sr-only">
      {{ esc_html__('Enter your coupon code and press Apply coupon.', 'woocommerce') }}
    </p>

    <div class="flex items-stretch gap-0">
      <label for="proxy_coupon_code" class="sr-only">
        {{ esc_html__('Coupon code', 'woocommerce') }}
      </label>

      <input
        id="proxy_coupon_code"
        type="text"
        name="proxy_coupon_code"
        class="input-text rounded-r-none!"
        placeholder="{{ esc_attr__('Coupon code', 'woocommerce') }}"
        inputmode="text"
        autocapitalize="off"
        autocorrect="off"
        spellcheck="false"
        autocomplete="off"
        required
        aria-required="true"
        aria-describedby="coupon-hint-proxy proxy-coupon-feedback"
      />

      <button
        type="submit"
        class="btn btn-outline rounded-l-none border-l-0"
        aria-describedby="coupon-hint-proxy"
        formnovalidate
      >
        {{ esc_html__('Apply coupon', 'woocommerce') }}
      </button>
    </div>

    <div
      id="proxy-coupon-feedback"
      class="sr-only text-sm mt-2 text-muted-foreground"
      role="status"
      aria-live="polite"
    ></div>
  </fieldset>
</form>

<x-separator />

@pushOnce('scripts')
<script>
(function () {
  // --- Real Woo coupon form selectors (cover both IDs just in case) ---
  const getRealForm = () =>
    document.getElementById('woocommerce-checkout-form-coupon')
    || document.getElementById('checkout_coupon'); // default Woo ID

  const getRealInput  = () => getRealForm()?.querySelector('input[name="coupon_code"]');
  const getRealSubmit = () => getRealForm()?.querySelector('button[name="apply_coupon"]');
  const getRealFB     = () => document.getElementById('coupon-feedback');

  // --- Proxy selectors ---
  const getProxyForm  = () => document.getElementById('proxy-coupon-form');
  const getProxyInput = () => document.getElementById('proxy_coupon_code');
  const getProxyFB    = () => document.getElementById('proxy-coupon-feedback');

  const ensureReady = () => !!(getRealForm() && getRealInput() && getRealSubmit());

  // Sync typing both ways (handles autofill too)
  document.addEventListener('input', (e) => {
    const t = e.target;
    if (!(t instanceof HTMLElement)) return;

    if (t.id === 'proxy_coupon_code') {
      const real = getRealInput();
      if (real) real.value = t.value;
    } else if (t.getAttribute('name') === 'coupon_code') {
      const proxy = getProxyInput();
      if (proxy) proxy.value = t.value;
    }
  });

  // --- HARD STOP: prevent the proxy submit from bubbling to checkout/Stripe ---
  // Capture phase ensures we win before Woo/Stripe listeners on document/body.
  document.addEventListener('submit', (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;

    // Only handle our proxy form
    if (form.id !== 'proxy-coupon-form') return;

    // Block everything else from reacting to this submit
    e.preventDefault();
    e.stopPropagation();
    if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();

    const proxyInput = getProxyInput();
    const code = proxyInput?.value.trim() || '';
    const proxyFB = getProxyFB();

    if (!code) {
      if (proxyFB) {
        proxyFB.textContent = '{{ esc_js(__('Please enter a coupon code.', 'woocommerce')) }}';
        proxyFB.classList.remove('sr-only');
      }
      proxyInput?.focus();
      return;
    }

    if (!ensureReady()) {
      if (proxyFB) {
        proxyFB.textContent = '{{ esc_js(__('Coupon form not ready on the page. Please try again.', 'woocommerce')) }}';
        proxyFB.classList.remove('sr-only');
      }
      return;
    }

    const realForm   = getRealForm();
    const realInput  = getRealInput();
    const realSubmit = getRealSubmit();

    if (!realForm || !realInput || !realSubmit) return;

    realInput.value = code;

    // Disable proxy button briefly to prevent double-clicks
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn?.setAttribute('disabled', 'disabled');

    try {
      // Submit ONLY the real coupon form so Woo runs its normal flow
      if (typeof realForm.requestSubmit === 'function') {
        realForm.requestSubmit(realSubmit);
      } else {
        // Fallbacks if requestSubmit not available
        realSubmit.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));
        realForm.submit();
      }
    } finally {
      setTimeout(() => submitBtn?.removeAttribute('disabled'), 1500);
    }
  }, true); // <-- capture

  // Safety net: some libs might intercept the button click; ensure we still route through submit
  document.addEventListener('click', (e) => {
    const target = e.target instanceof Element ? e.target : null;
    const btn = target?.closest('#proxy-coupon-form button[type="submit"]');
    if (!btn) return;

    // Prevent random click handlers from doing anything unexpected
    e.preventDefault();
    e.stopPropagation();
    if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();

    // Trigger the proxy form's submit (which our capture handler above owns)
    const proxyForm = getProxyForm();
    if (proxyForm) {
      if (typeof proxyForm.requestSubmit === 'function') {
        proxyForm.requestSubmit();
      } else {
        proxyForm.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
      }
    }
  }, true); // <-- capture

  // Mirror feedback from real coupon form (if your theme exposes it)
  const connectFeedbackMirror = () => {
    const realFB  = getRealFB();
    const proxyFB = getProxyFB();
    if (!realFB || !proxyFB) return;

    const mo = new MutationObserver(() => {
      const msg = realFB.textContent?.trim();
      if (msg) {
        proxyFB.textContent = msg;
        proxyFB.classList.remove('sr-only');
      }
    });
    mo.observe(realFB, { childList: true, subtree: true, characterData: true });
  };

  // Reconnect on DOM changes (handles morphing/re-renders)
  const pageMO = new MutationObserver(() => connectFeedbackMirror());
  pageMO.observe(document.documentElement, { childList: true, subtree: true });

  // Initial connect
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', connectFeedbackMirror);
  } else {
    connectFeedbackMirror();
  }
})();
</script>
@endPushOnce