// Node modules
import Alpine from "alpinejs";
import collapse from "@alpinejs/collapse";
import "htmx.org";

// Components
import accordionComponent from './components/accordion';
import carouselComponent from "./components/carousel";

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

// Register Alpine plugins
Alpine.plugin(collapse);

// Register Alpine components
Alpine.data('accordionComponent', accordionComponent);
Alpine.data('carouselComponent', carouselComponent);

// Start Alpine
Alpine.start();