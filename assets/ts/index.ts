// Import styles
import "../styles/index.css";

// Import node modules
import Alpine from "alpinejs";
import "htmx.org";

// Declare global types
declare global {
  interface Window {
    Alpine: typeof Alpine;
    htmx: any;
  }
}

// Assign Alpine to the window object
window.Alpine = Alpine;

// Initialize Alpine
Alpine.start();
