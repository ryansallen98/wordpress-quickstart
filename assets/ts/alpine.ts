// Import Alpine
import Alpine from "alpinejs";

// --- Global typings ---------------------------------------------------------
declare global {
  interface Window {
    Alpine?: typeof Alpine;
    AlpineData?: (name: string, factory: () => object) => void;
  }
}

// --- AlpineData helper: define BEFORE Alpine.start() ------------------------
type ComponentFactory = () => object;
type PendingComponent = { name: string; factory: ComponentFactory };

(function initAlpineDataHelper(w: Window) {
  // Don’t redefine if already present (e.g., another bundle/entry)
  if (!w.AlpineData) {
    const pending: PendingComponent[] = [];

    function flushPending() {
      if (!w.Alpine) return;
      const registeredNow = new Set<string>();
      while (pending.length) {
        const { name, factory } = pending.shift()!;
        if (registeredNow.has(name)) continue;
        w.Alpine.data(name, factory);
        registeredNow.add(name);
      }
    }

    w.AlpineData = (name: string, factory: ComponentFactory) => {
      if (w.Alpine) {
        w.Alpine.data(name, factory);
      } else {
        pending.push({ name, factory });
      }
    };

    // Try immediately (if Alpine already on window) and on alpine:init
    flushPending();
    w.addEventListener("alpine:init", flushPending, { once: true });
  }
})(window);

// --- Expose Alpine and start it --------------------------------------------
window.Alpine = Alpine;
Alpine.start();

// Optional exports if you want to import them elsewhere
export default Alpine;
export const AlpineData = window.AlpineData!;
