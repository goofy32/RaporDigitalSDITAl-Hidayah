import './bootstrap';
import 'flowbite';
import '@hotwired/turbo';
import Alpine from 'alpinejs';

// Alpine Stores
Alpine.store('navigation', {
    imagesLoaded: {},
    markImageLoaded(id) {
        this.imagesLoaded[id] = true;
    },
    isImageLoaded(id) {
        return this.imagesLoaded[id] || false;
    }
});

Alpine.store('notification', {
    items: [],
    unreadCount: 0,
    loading: false,

    async fetchNotifications() {
        if (this.loading) return;
        
        this.loading = true;
        try {
            const response = await fetch('/notifications');
            const data = await response.json();
            this.items = data;
            return data;
        } catch (error) {
            console.error('Error fetching notifications:', error);
            return [];
        } finally {
            this.loading = false;
        }
    },

    async fetchUnreadCount() {
        try {
            const response = await fetch('/notifications/unread-count');
            const data = await response.json();
            this.unreadCount = data.count;
            return data.count;
        } catch (error) {
            console.error('Error fetching unread count:', error);
            return 0;
        }
    },

    async markAsRead(notificationId) {
        try {
            await fetch(`/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            await this.fetchUnreadCount();
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }
});

// Alpine Components
Alpine.data('sessionTimeout', () => ({
    isExpired: false,
    timeoutDuration: 7200000,
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

Alpine.data('notificationHandler', () => ({
    isOpen: false,
    pollingInterval: null,

    init() {
        this.$store.notification.fetchNotifications();
        this.$store.notification.fetchUnreadCount();
        
        this.pollingInterval = setInterval(() => {
            this.$store.notification.fetchUnreadCount();
        }, 30000);
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

    async markAsRead(notificationId) {
        await this.$store.notification.markAsRead(notificationId);
    },

    destroy() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }
    }
}));

// Utility Functions
function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}
function updateSidebarActiveState() {
    try {
        const currentPath = window.location.pathname;
        const sidebarLinks = document.querySelectorAll('#logo-sidebar a[data-path]');
        
        if (!sidebarLinks.length) {
            return; // Silent fail jika tidak ada links
        }

        sidebarLinks.forEach(link => {
            const path = link.dataset.path;
            // Remove existing active classes
            link.classList.remove('bg-green-100', 'bg-gray-100', 'shadow-md');
            
            if (path && currentPath.includes(path)) {
                // Add active classes based on role
                if (currentPath.includes('admin')) {
                    link.classList.add('bg-green-100', 'shadow-md');
                } else {
                    link.classList.add('bg-gray-100');
                }
            }
        });

        // Handle dropdown for admin panel
        const dropdownButton = document.querySelector('[data-collapse-toggle="dropdown-rapor"]');
        if (dropdownButton && currentPath.includes('report-format')) {
            dropdownButton.classList.add('bg-green-100', 'shadow-md');
        }
    } catch (error) {
        console.error('Error updating sidebar state:', error);
    }
}


// Add manual sidebar update
document.addEventListener('DOMContentLoaded', updateSidebarActiveState);
document.addEventListener('turbo:render', updateSidebarActiveState);

const debouncedUpdateSidebar = debounce(updateSidebarActiveState, 100);

// Event Listeners
const eventListeners = {
    'turbo:load': () => {
        debouncedUpdateSidebar();
        initFlowbite();
        
        document.querySelectorAll('img[id]').forEach(img => {
            if (img.complete && img.naturalHeight !== 0) {
                Alpine.store('navigation').markImageLoaded(img.id);
            }
        });
    },

    'turbo:before-render': (event) => {
        ['topbar', 'sidebar', 'session-alert'].forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                const clone = element.cloneNode(true);
                event.detail.newBody.querySelector(`#${id}`)?.replaceWith(clone);
            }
        });

        const navigationStore = Alpine.store('navigation');
        event.detail.newBody.querySelectorAll('img[id]').forEach(img => {
            if (navigationStore.isImageLoaded(img.id)) {
                img.style.opacity = '1';
                img.setAttribute('data-turbo-cache', 'true');
            }
        });
    },

    'turbo:before-cache': () => {
        document.querySelectorAll('#logo-sidebar a').forEach(link => {
            link.classList.remove('bg-green-100', 'shadow-md');
        });
        window.formChanged = false;

        const notificationHandler = document.querySelector('[x-data="notificationHandler"]');
        if (notificationHandler && notificationHandler.__x) {
            notificationHandler.__x.destroy();
        }
    },

    'turbo:before-visit': (event) => {
        if (window.formChanged && !confirm('Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?')) {
            event.preventDefault();
        } else {
            window.formChanged = false;
        }
    },

    'turbo:before-fetch-response': async (event) => {
        const response = event.detail.fetchResponse;
        if (response.response.status === 401 || response.response.status === 419) {
            event.preventDefault();
            window.location.href = '/login';
        }
    },

    'turbo:visit': () => {
        debouncedUpdateSidebar();
    }
};




// Register event listeners
Object.entries(eventListeners).forEach(([event, handler]) => {
    document.addEventListener(event, handler);
});

window.Alpine = Alpine;
Alpine.start();