import './bootstrap';
import 'flowbite';
import '@hotwired/turbo';
import Alpine from 'alpinejs';

// Alpine Store untuk navigasi dan gambar
Alpine.store('navigation', {
    imagesLoaded: {},
    
    markImageLoaded(id) {
        this.imagesLoaded[id] = true;
    },
    
    isImageLoaded(id) {
        return this.imagesLoaded[id] || false;
    }
});

// Alpine Component untuk Session Timeout
Alpine.data('sessionTimeout', () => ({
    isExpired: false,
    timeoutDuration: 7200000, // 2 jam dalam milidetik
    checkInterval: null,

    init() {
        this.setupActivityTracking();
        this.setupSessionCheck();
        this.setupTurboListeners();
    },

    setupActivityTracking() {
        const resetActivity = () => {
            sessionStorage.setItem('lastActivityTime', Date.now().toString());
        };

        ['mousedown', 'keydown', 'scroll', 'touchstart', 'click'].forEach(event => {
            document.addEventListener(event, resetActivity, { passive: true });
        });

        resetActivity();
    },

    setupSessionCheck() {
        if (this.checkInterval) clearInterval(this.checkInterval);

        const checkSession = () => {
            const lastActivity = parseInt(sessionStorage.getItem('lastActivityTime') || Date.now());
            const inactive = Date.now() - lastActivity;

            if (inactive > this.timeoutDuration) {
                this.isExpired = true;
                clearInterval(this.checkInterval);
            }
        };

        this.checkInterval = setInterval(checkSession, 30000);
        checkSession();
    },

    setupTurboListeners() {
        document.addEventListener('turbo:load', () => {
            sessionStorage.setItem('lastActivityTime', Date.now().toString());
            this.setupSessionCheck();
        });

        document.addEventListener('turbo:before-cache', () => {
            if (this.checkInterval) {
                clearInterval(this.checkInterval);
            }
        });
    }
}));

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Update sidebar state
function updateSidebarActiveState() {
    try {
        const currentPath = window.location.pathname;
        const sidebarLinks = document.querySelectorAll('#logo-sidebar a');
        
        if (!sidebarLinks.length) {
            console.warn('Sidebar links not found');
            return;
        }

        sidebarLinks.forEach(link => {
            const path = link.dataset.path;
            if (path && currentPath.includes(path)) {
                link.classList.add('bg-green-100', 'shadow-md');
            } else {
                link.classList.remove('bg-green-100', 'shadow-md');
            }
        });

        const dropdownButton = document.querySelector('[data-collapse-toggle="dropdown-rapor"]');
        if (dropdownButton && currentPath.includes('report-format')) {
            dropdownButton.classList.add('bg-green-100', 'shadow-md');
        }
    } catch (error) {
        console.error('Error updating sidebar state:', error);
    }
}

const debouncedUpdateSidebar = debounce(updateSidebarActiveState, 100);

// Event Listeners
document.addEventListener('turbo:load', () => {
    debouncedUpdateSidebar();
    initFlowbite();
    
    // Initialize images state
    document.querySelectorAll('img[id]').forEach(img => {
        if (img.complete && img.naturalHeight !== 0) {
            Alpine.store('navigation').markImageLoaded(img.id);
        }
    });
});

document.addEventListener('turbo:before-render', (event) => {
    // Preserve permanent elements
    ['topbar', 'sidebar', 'session-alert'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            const clone = element.cloneNode(true);
            event.detail.newBody.querySelector(`#${id}`)?.replaceWith(clone);
        }
    });

    // Keep Alpine store state for images
    const navigationStore = Alpine.store('navigation');
    event.detail.newBody.querySelectorAll('img[id]').forEach(img => {
        if (navigationStore.isImageLoaded(img.id)) {
            img.style.opacity = '1';
            img.setAttribute('data-turbo-cache', 'true');
        }
    });

    // Preserve form changes status
    const currentFormChanged = window.formChanged;
    event.detail.newBody.querySelectorAll('script').forEach(script => {
        if (script.textContent.includes('window.formChanged')) {
            script.textContent = script.textContent.replace(
                'window.formChanged = false',
                `window.formChanged = ${currentFormChanged}`
            );
        }
    });
});

document.addEventListener('turbo:before-cache', () => {
    document.querySelectorAll('#logo-sidebar a').forEach(link => {
        link.classList.remove('bg-green-100', 'shadow-md');
    });
    window.formChanged = false;

    // Clear notification polling
    const notificationHandler = document.querySelector('[x-data="notificationHandler"]');
    if (notificationHandler && notificationHandler.__x) {
        notificationHandler.__x.destroy();
    }
});

// Form change handling
document.addEventListener('turbo:before-visit', (event) => {
    if (window.formChanged) {
        if (!confirm('Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?')) {
            event.preventDefault();
        } else {
            window.formChanged = false;
        }
    }
});

document.addEventListener('turbo:before-fetch-response', async (event) => {
    const response = event.detail.fetchResponse;
    if (response.response.status === 401 || response.response.status === 419) {
        event.preventDefault();
        window.location.href = '/login';
    }
});

document.addEventListener('turbo:before-fetch-request', (event) => {
    if (window.formChanged) {
        if (!confirm('Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?')) {
            event.preventDefault();
        } else {
            window.formChanged = false;
        }
    }
});

document.addEventListener('turbo:visit', () => {
    debouncedUpdateSidebar();
});

// Notifikasi Store dan Component
Alpine.store('notification', {
    items: [],
    unreadCount: 0,

    async fetchNotifications() {
        try {
            const response = await fetch('/notifications');
            const data = await response.json();
            this.items = data.notifications;
        } catch (error) {
            console.error('Error fetching notifications:', error);
        }
    },

    async fetchUnreadCount() {
        try {
            const response = await fetch('/notifications/unread-count');
            const data = await response.json();
            this.unreadCount = data.count;
        } catch (error) {
            console.error('Error fetching unread count:', error);
        }
    }
});

Alpine.data('notificationHandler', () => ({
    isOpen: false,
    pollingInterval: null,

    init() {
        this.$store.notification.fetchNotifications();
        this.$store.notification.fetchUnreadCount();
        
        // Polling untuk update count
        this.pollingInterval = setInterval(() => {
            this.$store.notification.fetchUnreadCount();
        }, 30000); // Update setiap 30 detik
    },

    toggleNotifications() {
        this.isOpen = !this.isOpen;
        if (this.isOpen) {
            this.$store.notification.fetchNotifications();
        }
    },

    formatDate(date) {
        return new Date(date).toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    destroy() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }
    }
}));

window.Alpine = Alpine;
Alpine.start();