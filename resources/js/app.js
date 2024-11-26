import './bootstrap';
import 'flowbite';
import '@hotwired/turbo';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// Clean up state when navigating with Turbo
document.addEventListener('turbo:before-render', () => {
    // Reset any Alpine.js components that might be in an open state
    document.querySelectorAll('[x-data]').forEach(el => {
        if (el.__x) {
            el.__x.toggles = {};
        }
    });
});

// Initialize Flowbite components after Turbo navigation
document.addEventListener('turbo:load', () => {
    initFlowbite();
});