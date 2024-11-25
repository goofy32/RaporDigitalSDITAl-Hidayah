import './bootstrap';
import 'flowbite';
import '@hotwired/turbo';
import Alpine from 'alpinejs';
import { Drawer, initFlowbite } from 'flowbite';


window.Alpine = Alpine;
Alpine.start();

// Keep track of initialized drawers
const initializedDrawers = new Set();

document.addEventListener('turbo:load', function() {
    // Initialize Flowbite components
    initFlowbite();
    // Initialize all dropdowns
    const dropdownButtons = document.querySelectorAll('[data-dropdown-toggle]');
    
    dropdownButtons.forEach(button => {
        const targetId = button.getAttribute('data-dropdown-toggle');
        const target = document.getElementById(targetId);
        const placement = button.getAttribute('data-dropdown-placement') || 'bottom';
        
        if (target) {
            // Toggle dropdown
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                target.classList.toggle('hidden');
                
                // Position the dropdown
                const buttonRect = button.getBoundingClientRect();
                if (placement === 'bottom-end') {
                    target.style.top = `${buttonRect.bottom + window.scrollY}px`;
                    target.style.right = '0';
                }
            });
        }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        const dropdowns = document.querySelectorAll('[data-dropdown-toggle]');
        dropdowns.forEach(button => {
            const targetId = button.getAttribute('data-dropdown-toggle');
            const target = document.getElementById(targetId);
            
            if (target && !button.contains(e.target) && !target.contains(e.target)) {
                target.classList.add('hidden');
            }
        });
    });
});

// Close dropdowns when navigating
document.addEventListener('turbo:before-render', () => {
    const dropdowns = document.querySelectorAll('[id^="user-dropdown"]');
    dropdowns.forEach(dropdown => {
        dropdown.classList.add('hidden');
    });
});

function initializeDrawer(element) {
    if (typeof window.Drawer !== 'undefined') {
        const drawer = new window.Drawer(element, {
            placement: 'left',
            backdrop: true,
            bodyScrolling: false,
            edge: false,
            edgeOffset: '',
            closeOnOutsideClick: true,
            onShow: () => {
                console.log('Drawer shown');
            },
            onHide: () => {
                console.log('Drawer hidden');
            }
        });

        element._drawer = drawer;
    }
}