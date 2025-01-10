import "./bootstrap";
import Alpine from "alpinejs";
import persist from "@alpinejs/persist";

// Initialize Alpine plugins
Alpine.plugin(persist);

window.Alpine = Alpine;
Alpine.start();
