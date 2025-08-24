// Import Alpine
import Alpine from "alpinejs";
import collapse from "@alpinejs/collapse";

// Extend Window interface for Alpine
declare global {
  interface Window {
    Alpine: typeof Alpine;
  }
}

// Plugins
Alpine.plugin(collapse);

// Initialize Alpine
window.Alpine = Alpine;
Alpine.start();
