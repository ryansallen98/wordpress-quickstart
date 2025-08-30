// Node modules
import Alpine from "alpinejs";
import collapse from "@alpinejs/collapse";
import "htmx.org";

// Import assets
import.meta.glob([
  '../images/**',
  '../fonts/**',
]);

// Extend Window interface for Alpine
declare global {
  interface Window {
    Alpine: typeof Alpine;
  }
}

// Initialize Alpine
window.Alpine = Alpine;
Alpine.plugin(collapse);
Alpine.start();