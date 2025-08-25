// entry.js
import "../styles/index.css";

// Extend Window interface for htmx and Alpine
declare global {
  interface Window {
    htmx: typeof htmx;
    Alpine: typeof Alpine;
  }
}

// Ensure htmx is actually bundled
import htmx from "htmx.org";
window.htmx = htmx;

// Alpine boot once
import Alpine from "alpinejs";
import collapse from "@alpinejs/collapse";
window.Alpine = Alpine;
Alpine.plugin(collapse);
Alpine.start();

// Swup (v3 or v4). If you use multiple containers, list them here.
import Swup from "swup";
// OPTIONAL: only if you rely on inline <script> in loaded pages
// import SwupScriptsPlugin from "@swup/scripts-plugin";

const swup = new Swup({
  containers: ["#swup", "[data-swup]"], // adjust to match your markup
  // plugins: [new SwupScriptsPlugin()],
});

// Helper to (re)bind just the swapped roots
type RebindRoot = HTMLElement;

function rebind(root: RebindRoot | Document | null): void {
  if (!root) return;
  const element = root instanceof Document ? root.body : root;
  if (window.htmx) window.htmx.process(element);
  if (window.Alpine) window.Alpine.initTree(element);
}

// Get the live container elements Swup manages
function getRoots() {
  const sels = swup.options?.containers || ["#swup"];
  return sels.map((sel) => document.querySelector(sel)).filter(Boolean);
}

// --- Swup v4 hooks ---
if (swup.hooks?.on) {
  const run = () => {
    const roots = getRoots();
    (roots.length ? roots : [document]).forEach((root) =>
      rebind(root as Document | HTMLElement | null),
    );
  };

  // After new DOM is in place and visible
  swup.hooks.on("page:view", run);
  // Some plugins fire earlier; this catches those, too
  swup.hooks.on("content:replace", run);
} else {
  // --- Swup v3 fallback DOM event ---
  document.addEventListener("swup:contentReplaced", () => {
    const roots = getRoots();
    (roots.length ? roots : [document]).forEach((root) =>
      rebind(root as Document | HTMLElement | null),
    );
  });
}

// (Optional) sanity check: see your events actually firing
// ;["swup:contentReplaced","swup:page:view"].forEach(evt =>
//   document.addEventListener(evt, () => console.log(`[${evt}]`)));
