// Node modules
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import 'htmx.org';
import * as FloatingUIDOM from '@floating-ui/dom';

import './lib/toast';
import './lib/wc-toast';

// Import assets
import.meta.glob(['../images/**', '../fonts/**']);

// ---- Lazy component loaders (one chunk per component) ----
const componentLoaders: Record<string, () => Promise<{ default: any }>> = {
  accordionComponent: () => import('./components/accordion'),
  carouselComponent: () => import('./components/carousel'),
  selectComponent: () => import('./components/select'),
};

// Extend Window interface for Alpine
declare global {
  interface Window {
    Alpine: typeof Alpine;
    FloatingUIDOM: typeof FloatingUIDOM;
  }
}

// Initialize Window
window.Alpine = Alpine;
window.FloatingUIDOM = FloatingUIDOM;

// Register Alpine plugins (small; ok to eager-load)
Alpine.plugin(focus);
Alpine.plugin(collapse);

/**
 * Scan for x-data and pre-register only the components actually used
 * on this page before starting Alpine.
 */
async function registerUsedComponents() {
  const used = new Set<string>();
  document.querySelectorAll<HTMLElement>('[x-data]').forEach((el) => {
    const expr = el.getAttribute('x-data') || '';
    for (const name in componentLoaders) {
      // naive but effective: check if the component name appears in the expression
      if (expr.includes(name)) used.add(name);
    }
  });

  await Promise.all(
    [...used].map(async (name) => {
      const mod = await componentLoaders[name]();
      Alpine.data(name, mod.default);
    }),
  );
}

(async () => {
  await registerUsedComponents();
  Alpine.start();
})();


