// Import dependencies
import './bootstrap';
import 'flowbite';
import '@hotwired/turbo';
import Alpine from 'alpinejs';

const cleanupHandlers = new Set();

document.addEventListener('alpine:init', () => {
    Alpine.store('sidebar', {
        dropdownState: {
            formatRapor: false
        },
        
        toggleDropdown(name) {
            this.dropdownState[name] = !this.dropdownState[name];
            localStorage.setItem(`dropdown_${name}`, this.dropdownState[name]);
        },

        initDropdown(name) {
            const savedState = localStorage.getItem(`dropdown_${name}`);
            if (savedState !== null) {
                this.dropdownState[name] = savedState === 'true';
            }
        }
    });

    Alpine.data('placeholderGuide', () => ({
        placeholderSearch: '',
        activeCategory: 'siswa',
        
        init() {
            // Initialize any required data
        },
        
        copyPlaceholder(text) {
            navigator.clipboard.writeText(text)
                .then(() => {
                    this.showFeedback('success', 'Placeholder berhasil disalin');
                })
                .catch(() => {
                    this.showFeedback('error', 'Gagal menyalin placeholder');
                });
        }
    }));
});

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

// Tambahkan store baru untuk rapor
Alpine.store('report', {
    template: null,
    loading: false,
    error: null,
    feedback: null,
    
    // Fetch template aktif
    async fetchActiveTemplate(type) {
        if (this.loading) return;
        
        this.loading = true;
        try {
            const response = await fetch(`/admin/report-template/${type}/active`);
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            this.template = data.template;
        } catch (error) {
            console.error('Error fetching template:', error);
            this.error = error.message;
        } finally {
            this.loading = false;
        }
    },

    // Upload template baru
    async uploadTemplate(file, type) {
        if (!file || this.loading) return;

        const formData = new FormData();
        formData.append('template', file);
        formData.append('type', type);

        this.loading = true;
        try {
            const response = await fetch('/admin/report-template/upload', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();
            if (!response.ok) throw new Error(result.message);

            this.template = result.template;
            this.setFeedback('Template berhasil diupload', 'success');
            return true;
        } catch (error) {
            this.setFeedback(error.message, 'error');
            return false;
        } finally {
            this.loading = false;
        }
    },

    // Aktivasi template
    async activateTemplate(templateId) {
        if (this.loading) return;

        this.loading = true;
        try {
            const response = await fetch(`/admin/report-template/${templateId}/activate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();
            if (!response.ok) throw new Error(result.message);

            if (this.template && this.template.id === templateId) {
                this.template.is_active = true;
            }
            this.setFeedback('Template berhasil diaktifkan', 'success');
            return true;
        } catch (error) {
            this.setFeedback(error.message, 'error');
            return false;
        } finally {
            this.loading = false;
        }
    },

    async downloadSampleTemplate() {
        try {
            const response = await fetch('/admin/report-template/sample');
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'template_rapor_sample.docx';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        } catch (error) {
            this.setFeedback('Gagal mengunduh template contoh', 'error');
        }
    },

    // Generate rapor
    async generateReport(siswaId, type) {
        if (this.loading) return;

        this.loading = true;
        try {
            const response = await fetch(`/wali-kelas/rapor/generate/${siswaId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ type })
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message);
            }

            // Handle file download
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `rapor_${type.toLowerCase()}.docx`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);

            this.setFeedback('Rapor berhasil digenerate', 'success');
            return true;
        } catch (error) {
            this.setFeedback(error.message, 'error');
            return false;
        } finally {
            this.loading = false;
        }
    },

    // Helper untuk feedback
    setFeedback(message, type = 'success') {
        this.feedback = { message, type };
        setTimeout(() => {
            this.feedback = null;
        }, 3000);
    }
});

// Tambahkan component untuk manajemen rapor
Alpine.data('reportTemplateManager', (config) => ({
    type: config.type,
    templates: config.templates || [],
    activeTemplate: config.activeTemplate,
    loading: false,
    showPlaceholderGuide: false,
    feedback: {
        type: '',
        message: ''
    },

    init() {
        console.log('Initializing with:', {
            type: this.type,
            templatesCount: this.templates.length,
            templates: this.templates
        });
    },

    async handleFileUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        if (!file.name.endsWith('.docx')) {
            this.showFeedback('error', 'File harus berformat .docx');
            return;
        }

        const formData = new FormData();
        formData.append('template', file);
        formData.append('type', this.type);

        try {
            this.loading = true;
            const response = await fetch('/admin/report-template/upload', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();
            
            if (result.success) {
                // Tambahkan template baru ke daftar
                this.templates = [result.template, ...this.templates];
                this.showFeedback('success', 'Template berhasil diupload');
                event.target.value = '';
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            this.showFeedback('error', error.message || 'Gagal mengupload template');
        } finally {
            this.loading = false;
        }
    },

    async activateTemplate(template) {
        try {
            this.loading = true;
            const response = await fetch(`/admin/report-template/${template.id}/activate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();
            
            if (result.success) {
                // Update status aktif untuk semua template
                this.templates = this.templates.map(t => ({
                    ...t,
                    is_active: t.id === template.id
                }));
                this.activeTemplate = template;
                this.showFeedback('success', 'Template berhasil diaktifkan');
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            this.showFeedback('error', error.message || 'Gagal mengaktifkan template');
        } finally {
            this.loading = false;
        }
    },

    async deleteTemplate(template) {
        if (!confirm('Anda yakin ingin menghapus template ini?')) return;

        try {
            this.loading = true;
            const response = await fetch(`/admin/report-template/${template.id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();
            
            if (result.success) {
                // Hapus template dari daftar
                this.templates = this.templates.filter(t => t.id !== template.id);
                if (this.activeTemplate?.id === template.id) {
                    this.activeTemplate = null;
                }
                this.showFeedback('success', 'Template berhasil dihapus');
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            this.showFeedback('error', error.message || 'Gagal menghapus template');
        } finally {
            this.loading = false;
        }
    },

    previewTemplate(template) {
        window.open(`/admin/report-template/${template.id}/preview`, '_blank');
    },

    openPlaceholderGuide() {
        this.showPlaceholderGuide = true;
    },

    showFeedback(type, message) {
        this.feedback = { type, message };
        setTimeout(() => {
            this.feedback.message = '';
        }, 3000);
    },

    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    }
}));

Alpine.store('formProtection', {
    formChanged: false,
    isSubmitting: false,
    
    init() {
        // Tambahkan pengecualian untuk halaman login
        if (this.isLoginPage()) {
            return; // Tidak menerapkan form protection di halaman login
        }

        // Hanya setup untuk halaman dengan form yang membutuhkan protection
        if (document.querySelector('form[data-needs-protection]')) {
            this.setupFormChangeListeners();
            this.setupNavigationProtection();
        }
    },
    
    isLoginPage() {
        // Cek apakah ini halaman login
        return window.location.pathname === '/login' || 
               document.querySelector('form[action*="login"]') !== null;
    },
    
    setupFormChangeListeners() {
        document.querySelectorAll('form[data-needs-protection] input, form[data-needs-protection] select, form[data-needs-protection] textarea').forEach(element => {
            element.addEventListener('change', () => this.formChanged = true);
            element.addEventListener('keyup', () => this.formChanged = true);
        });
    },
    
    setupNavigationProtection() {
        window.addEventListener('beforeunload', (e) => {
            if (this.formChanged && !this.isSubmitting) {
                e.preventDefault();
                e.returnValue = 'Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?';
                return e.returnValue;
            }
        });
    },
    
    markAsChanged() {
        this.formChanged = true;
    },
    
    startSubmitting() {
        this.isSubmitting = true;
    },
    
    reset() {
        this.formChanged = false;
        this.isSubmitting = false;
    }
});

// Improved FormProtection Component
Alpine.data('formProtection', () => ({
    init() {
        if (!this.$el.tagName === 'FORM') {
            return; // Only initialize on form elements
        }

        // Setup form specific listeners
        this.$el.querySelectorAll('input, select, textarea').forEach(element => {
            element.addEventListener('change', () => {
                this.$store.formProtection.markAsChanged();
            });
            element.addEventListener('keyup', () => {
                this.$store.formProtection.markAsChanged();
            });
        });

        // Handle form submission
        this.$el.addEventListener('submit', () => {
            this.$store.formProtection.startSubmitting();
        });
    },

    handleSubmit(e) {
        if (this.$store.formProtection.formChanged) {
            if (!confirm('Apakah Anda yakin ingin menyimpan perubahan?')) {
                e.preventDefault();
                return;
            }
        }
        this.$store.formProtection.startSubmitting();
    },

    confirmClear() {
        if (this.$store.formProtection.formChanged) {
            return confirm('Apakah Anda yakin ingin membersihkan form? Perubahan yang belum disimpan akan hilang.');
        }
        return true;
    }
}));

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
    
        if (typeof initFlowbite === 'function') {
            initFlowbite();
        }
    
        // Perbaiki bagian ini
        const handler = debounce(updateSidebarActiveState, 100);
        document.addEventListener('turbo:render', handler);
        cleanupHandlers.add(() => {
            document.removeEventListener('turbo:render', handler);
        });
    
        // Restore dropdown state
        const savedState = localStorage.getItem('formatRaporDropdown');
        if (savedState) {
            Alpine.store('sidebar').dropdownState.formatRapor = savedState === 'true';
        }
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

    const reportManager = document.querySelector('[x-data="reportManager"]');
    if (reportManager && reportManager.__x) {
        reportManager.__x.destroy();
    }

    const dropdowns = document.querySelectorAll('[x-data]');
    dropdowns.forEach(dropdown => {
        if (dropdown.__x) {
            const state = dropdown.__x.$data.openDropdown;
            if (typeof state !== 'undefined') {
                localStorage.setItem('formatRaporDropdown', state);
            }
        }
    });
});

// Initialize Alpine
window.Alpine = Alpine;
Alpine.start();