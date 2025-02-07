// Import dependencies
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

// Notification Store dengan Real-time Updates
Alpine.store('notification', {
    items: [],
    unreadCount: 0,
    loading: false,
    refreshInterval: null,

    // Ambil semua notifikasi
    async fetchNotifications() {
        if (this.loading) return;
        
        this.loading = true;
        try {
            const path = window.location.pathname;
            let url;
            
            // Perbaiki URL sesuai dengan routes yang baru
            if (path.includes('/admin/')) {
                url = '/admin/information/list';
            } else if (path.includes('/pengajar/')) {
                url = '/pengajar/notifications';
            } else if (path.includes('/wali-kelas/')) {
                url = '/wali-kelas/notifications';
            }
            
            if (!url) return;

            const response = await fetch(url);
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            this.items = data.items || [];
            
            // Log untuk debugging
            console.log('Fetched notifications:', this.items);
        } catch (error) {
            console.error('Error fetching notifications:', error);
        } finally {
            this.loading = false;
        }
    },

    // Perbaiki URL untuk markAsRead
    async markAsRead(notificationId) {
        try {
            const path = window.location.pathname;
            let baseUrl = '';
            
            if (path.includes('/pengajar/')) {
                baseUrl = '/pengajar/notifications';
            } else if (path.includes('/wali-kelas/')) {
                baseUrl = '/wali-kelas/notifications';
            }
            
            if (!baseUrl) return;

            const response = await fetch(`${baseUrl}/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            if (response.ok) {
                this.items = this.items.map(item => {
                    if (item.id === notificationId) {
                        return { ...item, is_read: true };
                    }
                    return item;
                });
                
                await this.fetchUnreadCount();
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    },

    // Ambil jumlah notifikasi yang belum dibaca
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

    // Tambah notifikasi baru (untuk admin)
    async addNotification(notification) {
        try {
            const response = await fetch('/admin/information', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(notification)
            });
            
            if (response.ok) {
                await this.fetchNotifications();
                await this.fetchUnreadCount();
                return true;
            }
            return false;
        } catch (error) {
            console.error('Error adding notification:', error);
            return false;
        }
    },

    // Hapus notifikasi (untuk admin)
    async deleteNotification(id) {
        try {
            const response = await fetch(`/admin/information/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (response.ok) {
                this.items = this.items.filter(item => item.id !== id);
                await this.fetchUnreadCount();
                return true;
            }
            return false;
        } catch (error) {
            console.error('Error deleting notification:', error);
            return false;
        }
    },

    // Mulai interval pembaruan otomatis
    startAutoRefresh() {
        this.stopAutoRefresh(); // Hentikan interval yang mungkin sudah berjalan
        this.refreshInterval = setInterval(() => {
            this.fetchNotifications();
            this.fetchUnreadCount();
        }, 30000); // Update setiap 30 detik
    },

    // Hentikan interval pembaruan otomatis
    stopAutoRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    }
});

// Alpine Components
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

// Notification Handler Component
Alpine.data('notificationHandler', () => ({
    showModal: false,
    isOpen: false,
    errorMessage: '',
    successMessage: '',
    notificationForm: {
        title: '',
        content: '',
        target: '',
        specific_users: []
    },

    init() {
        this.$store.notification.fetchNotifications();
        this.$store.notification.fetchUnreadCount();
        this.$store.notification.startAutoRefresh();
    },

    async submitNotification() {
        try {
            const result = await this.$store.notification.addNotification(this.notificationForm);
            
            if (result) {
                this.successMessage = 'Notifikasi berhasil ditambahkan';
                this.resetForm();
                this.showModal = false; // Tutup modal
                await this.$store.notification.fetchNotifications(); // Refresh notifikasi
            } else {
                this.errorMessage = 'Gagal menambahkan notifikasi';
            }
        } catch (error) {
            console.error('Error:', error);
            this.errorMessage = 'Terjadi kesalahan';
        }

        setTimeout(() => {
            this.successMessage = '';
            this.errorMessage = '';
        }, 3000);
    },


    resetForm() {
        this.notificationForm = {
            title: '',
            content: '',
            target: '',
            specific_users: []
        };
    },

    toggleNotifications() {
        this.isOpen = !this.isOpen;
        if (this.isOpen) {
            this.$store.notification.fetchNotifications();
        }
    },

    destroy() {
        this.$store.notification.stopAutoRefresh();
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

// Sidebar Active State Handler
function updateSidebarActiveState() {
    try {
        const currentPath = window.location.pathname;
        const sidebarLinks = document.querySelectorAll('#logo-sidebar a[data-path]');
        
        if (!sidebarLinks.length) return;

        sidebarLinks.forEach(link => {
            const path = link.dataset.path;
            link.classList.remove('bg-green-100', 'bg-gray-100', 'shadow-md');
            
            if (path && currentPath.includes(path)) {
                if (currentPath.includes('admin')) {
                    link.classList.add('bg-green-100', 'shadow-md');
                } else {
                    link.classList.add('bg-gray-100');
                }
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

// Event Listeners
const debouncedUpdateSidebar = debounce(updateSidebarActiveState, 100);

document.addEventListener('DOMContentLoaded', () => {
    updateSidebarActiveState();
    if (typeof initFlowbite === 'function') {
        initFlowbite();
    }
});

document.addEventListener('turbo:render', updateSidebarActiveState);
document.addEventListener('turbo:visit', debouncedUpdateSidebar);

// Cleanup listeners
document.addEventListener('turbo:before-cache', () => {
    const notificationHandler = document.querySelector('[x-data="notificationHandler"]');
    if (notificationHandler && notificationHandler.__x) {
        notificationHandler.__x.destroy();
    }
});

// Initialize Alpine
window.Alpine = Alpine;
Alpine.start();