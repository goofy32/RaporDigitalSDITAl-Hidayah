// Import dependencies
import './bootstrap';
import 'flowbite';
import '@hotwired/turbo';
import Alpine from 'alpinejs';
import { renderAsync } from 'docx-preview';

window.renderAsync = renderAsync;
const cleanupHandlers = new Set();
const sidebarImageCache = new Map();

document.addEventListener('turbo:load', () => {
    // Handle search forms dengan atribut data-turbo-search
    const searchForms = document.querySelectorAll('form[data-turbo-search]');
    
    searchForms.forEach(form => {
        const searchInput = form.querySelector('input[name="search"]');
        const submitButton = form.querySelector('button[type="submit"]');
        
        if (!searchInput || !submitButton) return;
        
        // Submit form saat user menekan Enter
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                
                // Tampilkan loading indicator
                submitButton.innerHTML = `
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                `;
                
                // Submit form dengan Turbo
                form.requestSubmit();
            }
        });
        
        // Handle click pada tombol submit
        form.addEventListener('submit', function(e) {
            // Jangan gunakan e.preventDefault() di sini agar form tetap submit
            
            // Tampilkan loading indicator
            const originalContent = submitButton.innerHTML;
            submitButton.innerHTML = `
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            `;
            
            // Kembalikan tombol ke normal setelah navigasi selesai
            document.addEventListener('turbo:render', function restoreButton() {
                submitButton.innerHTML = originalContent;
                document.removeEventListener('turbo:render', restoreButton);
            });
        });
    });
});


document.addEventListener('turbo:render', () => {
    const searchForms = document.querySelectorAll('form[data-turbo-search]');
    
    searchForms.forEach(form => {
        const searchInput = form.querySelector('input[name="search"]');
        if (searchInput && searchInput.value) {
            // Fokus dan seleksi text pada input search
            setTimeout(() => {
                searchInput.focus();
                searchInput.select();
            }, 100);
        }
    });
});

document.addEventListener('turbo:before-render', (event) => {
    // Preserve sidebar images state
    const sidebar = document.getElementById('logo-sidebar');
    if (sidebar) {
        // Mark sidebar as processed to avoid duplicates
        sidebar.setAttribute('data-turbo-processed', 'true');
        
        // Find matching sidebar in new body and replace it with current one
        const newSidebar = event.detail.newBody.querySelector('#logo-sidebar');
        if (newSidebar) {
            // Force all images in current sidebar to be visible
            sidebar.querySelectorAll('img').forEach(img => {
                img.style.opacity = '1';
                img.style.visibility = 'visible';
                img.setAttribute('data-loaded', 'true');
                sidebarImageCache.set(img.src, true);
            });
            
            // Replace new sidebar with current one that has visible images
            newSidebar.replaceWith(sidebar);
        }
    }
});

document.addEventListener('turbo:before-render', () => {
    // Bersihkan state Alpine
    Alpine.store('report').showPreview = false;
    Alpine.store('report').previewContent = '';
    Alpine.store('report').closePreview();

    if (window.Alpine) {
        window.Alpine.flushAndStopDeferring();
    }
});

Alpine.data('geminiChat', () => ({
    isOpen: false,
    message: '',
    chats: [],
    isLoading: false,
    error: null,
    
    init() {
        this.loadHistory();
    },
    
    toggleChat() {
        this.isOpen = !this.isOpen;
        if (this.isOpen && this.chats.length === 0) {
            this.loadHistory();
        }
    },
    
    async loadHistory() {
        try {
            const response = await fetch('/admin/gemini/history');
            const data = await response.json();
            
            if (data.success) {
                this.chats = data.chats.reverse(); // Reverse agar yang terbaru ada di bawah
            }
        } catch (error) {
            console.error('Error loading chat history:', error);
            this.error = 'Gagal memuat riwayat chat';
        }
    },
    
    async sendMessage() {
        if (!this.message.trim()) return;
        
        const userMessage = this.message;
        this.message = '';
        this.isLoading = true;
        
        // Tambahkan pesan user ke chat list
        this.chats.push({
            message: userMessage,
            response: '...',
            created_at: new Date().toISOString()
        });
        
        // Scroll ke bawah
        this.$nextTick(() => {
            const chatContainer = this.$refs.chatContainer;
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        });
        
        try {
            const response = await fetch('/admin/gemini/send-message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ message: userMessage })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Update chat terakhir dengan response
                this.chats[this.chats.length - 1].response = data.response;
            } else {
                // Update chat terakhir dengan pesan error
                this.chats[this.chats.length - 1].response = 'Maaf, terjadi kesalahan saat memproses pesan Anda.';
            }
        } catch (error) {
            console.error('Error sending message:', error);
            // Update chat terakhir dengan pesan error
            this.chats[this.chats.length - 1].response = 'Maaf, terjadi kesalahan saat mengirim pesan.';
        } finally {
            this.isLoading = false;
            
            // Scroll ke bawah lagi setelah respons
            this.$nextTick(() => {
                const chatContainer = this.$refs.chatContainer;
                if (chatContainer) {
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }
            });
        }
    }
}));

document.addEventListener('turbo:frame-render', () => {
    Alpine.store('pageLoading').stopLoading();
});

document.addEventListener('turbo:request-timeout', () => {
    Alpine.store('pageLoading').stopLoading();
    console.warn('Turbo request timed out');
});

document.addEventListener('turbo:before-fetch-request', (event) => {
    // Set timeout untuk fetch lebih lama untuk jaringan lambat
    event.detail.fetchOptions.timeout = 10000; // 10 detik
});

document.addEventListener('turbo:load', () => {
    const imgObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                
                // Jika gambar sudah dimuat sebelumnya
                if (img.dataset.loaded === 'true' || sidebarImageCache.has(img.src)) {
                    img.style.opacity = '1';
                    imgObserver.unobserve(img);
                    return;
                }
                
                // Tunggu gambar selesai dimuat
                img.onload = () => {
                    img.style.opacity = '1';
                    img.dataset.loaded = 'true';
                    sidebarImageCache.set(img.src, true);
                };
                
                imgObserver.unobserve(img);
            }
        });
    });
    
    // Terapkan observer pada semua gambar di sidebar
    document.querySelectorAll('#logo-sidebar img').forEach(img => {
        // Set opacity awal untuk mencegah flickering
        if (!img.dataset.loaded && !sidebarImageCache.has(img.src)) {
            img.style.opacity = '0';
            img.style.transition = 'opacity 0.2s';
        }
        
        imgObserver.observe(img);
    });

    if (typeof initFlowbite === 'function') {
        initFlowbite();
    }
    
    // Jika Alpine sudah diinisialisasi, cukup perbarui DOM
    if (window.Alpine && window.alpineInitialized) {
        window.Alpine.initTree(document.body);
    }

    if (window.Alpine) {
        const savedState = localStorage.getItem('sidebar_dropdown_state');
        if (savedState) {
            try {
                const state = JSON.parse(savedState);
                Alpine.store('sidebar').dropdownState = { ...Alpine.store('sidebar').dropdownState, ...state };
            } catch (e) {
                console.error('Error restoring sidebar state:', e);
            }
        }
    }


    setTimeout(() => {
        if (Alpine.store('pageLoading')) {
            Alpine.store('pageLoading').stopLoading();
        }
    }, 100);

});

document.addEventListener('turbo:submit-start', (event) => {
    const form = event.target;
    if (form.hasAttribute('data-needs-protection')) {
        // Clear previous validation errors
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        document.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Monitor form submissions for the student add form
    const studentForm = document.querySelector('form[action*="student.store"]');
    
    if (studentForm) {
        console.log('Student form detected, adding monitoring');
        
        studentForm.addEventListener('submit', function(e) {
            console.log('Form submission detected');
            
            // Log form data for debugging
            const formData = new FormData(this);
            console.log('Form data:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }
            
            // Check if tahun_ajaran_id is present
            if (!formData.get('tahun_ajaran_id')) {
                console.warn('tahun_ajaran_id is missing!');
                // Optional: Add the value if missing
                const tahunAjaranId = document.querySelector('meta[name="tahun-ajaran-id"]')?.content;
                if (tahunAjaranId) {
                    console.log(`Adding tahun_ajaran_id: ${tahunAjaranId}`);
                    
                    // Create hidden input if it doesn't exist
                    if (!document.querySelector('input[name="tahun_ajaran_id"]')) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'tahun_ajaran_id';
                        input.value = tahunAjaranId;
                        this.appendChild(input);
                    }
                }
            }
        });
    }
    
    // Check for Alpine.js formProtection
    const formProtectionEl = document.querySelector('[x-data="formProtection"]');
    if (formProtectionEl) {
        console.log('Form protection detected');
    } else {
        console.warn('Form protection not found on the page');
    }
    
    // Check for Turbo navigation issues
    document.addEventListener('turbo:before-visit', () => {
        console.log('Turbo navigation started');
    });
    
    document.addEventListener('turbo:before-cache', () => {
        console.log('Page being cached by Turbo');
    });
    
    document.addEventListener('turbo:submit-start', (event) => {
        console.log('Form submission via Turbo:', event.detail.formSubmission);
    });
    
    document.addEventListener('turbo:submit-end', (event) => {
        console.log('Form submission completed:', event.detail.success ? 'success' : 'failure');
        if (!event.detail.success) {
            console.error('Form submission failed');
        }
    });
});

document.addEventListener('turbo:submit-end', (event) => {
    if (!event.detail.success) {
        // Handle failed form submission
        console.error('Form submission failed');
        
        // If there's a validation error alert in the response, show it
        const responseText = event.detail.fetchResponse.responseText;
        if (responseText && responseText.includes('bg-red-100')) {
            // Extract and insert the validation error element
            const parser = new DOMParser();
            const htmlDoc = parser.parseFromString(responseText, 'text/html');
            const errorElement = htmlDoc.querySelector('.bg-red-100.border-l-4.border-red-500');
            
            if (errorElement) {
                const formElement = event.target;
                formElement.insertAdjacentElement('beforebegin', errorElement);
                
                // Scroll to error
                errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }
});


document.addEventListener('turbo:before-fetch-response', (event) => {
    const response = event.detail.fetchResponse;
    if (!response.succeeded) {
        Alpine.store('pageLoading').stopLoading();
        console.error('Turbo fetch failed:', response.statusCode);
    }
});

document.addEventListener('turbo:render', () => {
    // Find all sidebar images and ensure they're visible
    const sidebarImages = document.querySelectorAll('#logo-sidebar img');
    sidebarImages.forEach(img => {
        // If the image is in our cache or marked as loaded, ensure it's visible
        if (sidebarImageCache.has(img.src) || img.getAttribute('data-loaded') === 'true') {
            img.style.opacity = '1';
            img.style.visibility = 'visible';
            img.setAttribute('data-loaded', 'true');
        } else {
            // For images not yet loaded, set up proper loading behavior
            img.style.opacity = '0';
            img.onload = () => {
                img.style.opacity = '1';
                img.style.visibility = 'visible';
                img.setAttribute('data-loaded', 'true');
                sidebarImageCache.set(img.src, true);
            };
        }
    });
});

function preloadAndCacheSidebarIcons() {
    const sidebar = document.getElementById('logo-sidebar');
    if (!sidebar) return;

    // Force preload all sidebar images
    sidebar.querySelectorAll('img').forEach(img => {
        // Ensure image is visible
        img.style.opacity = '1';
        img.style.visibility = 'visible';
        
        // If not already loaded or cached
        if (!img.dataset.loaded && !sidebarImageCache.has(img.src)) {
            // Add to cache even before loading completes
            sidebarImageCache.set(img.src, true);
            img.setAttribute('data-loaded', 'loading');
            
            // Create a new image element to preload
            const preloader = new Image();
            preloader.onload = () => {
                img.setAttribute('data-loaded', 'true');
                img.style.opacity = '1';
            };
            preloader.onerror = () => {
                // Try reloading with cache buster
                const cacheBuster = `${img.src}${img.src.includes('?') ? '&' : '?'}v=${Date.now()}`;
                img.src = cacheBuster;
                img.setAttribute('data-loaded', 'retrying');
            };
            preloader.src = img.src;
        }
    });
    
    // Log state to console for debugging
    console.log('Sidebar image cache state:', 
        [...sidebarImageCache.keys()].map(key => key.split('/').pop()));
}

// Call this function at key points
document.addEventListener('DOMContentLoaded', preloadAndCacheSidebarIcons);
document.addEventListener('turbo:load', preloadAndCacheSidebarIcons);
document.addEventListener('turbo:render', preloadAndCacheSidebarIcons);



document.addEventListener('alpine:init', () => {
    Alpine.store('sidebar', {
        dropdownState: {

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

    Alpine.store('keyboardShortcut', {
        logoutKeyCombination: {
            key: 'l',
            ctrlKey: true,
            altKey: false
        },

        init() {
            document.addEventListener('keydown', (event) => {
                if (
                    event.key === this.logoutKeyCombination.key &&
                    event.ctrlKey === this.logoutKeyCombination.ctrlKey &&
                    event.altKey === this.logoutKeyCombination.altKey
                ) {
                    event.preventDefault();
                    this.confirmLogout();
                }
            });
        },

        confirmLogout() {
            // Tampilkan konfirmasi sebelum logout
            const confirmed = confirm('Apakah Anda yakin ingin logout?');
            if (confirmed) {
                this.logout();
            }
        },

        async logout() {
            try {
                const response = await fetch('/logout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    window.location.href = '/login';
                } else {
                    console.error('Logout gagal');
                    alert('Gagal logout. Silakan coba lagi.');
                }
            } catch (error) {
                console.error('Error logout:', error);
                alert('Terjadi kesalahan saat logout');
            }
        }
    });
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
    previewContent: '',
    showPreview: false,

    async downloadPdf(siswaId) {
        try {
            const response = await fetch(`/wali-kelas/rapor/download-pdf/${siswaId}`);
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `rapor_${siswaId}.pdf`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        } catch (error) {
            this.setFeedback('Gagal mengunduh PDF', 'error');
        }
    },
    showPreviewModal(siswaId) {
        return this.handleAsync(async () => {
            const response = await fetch(`/wali-kelas/rapor/preview/${siswaId}`);
            const data = await response.json();
            
            if (data.success) {
                this.previewContent = data.html;
                this.showPreview = true;
            } else {
                throw new Error(data.message);
            }
        });
    },

    async handleAsync(operation) {
        try {
            this.loading = true;
            await operation();
        } catch (error) {
            this.setFeedback(error.message, 'error');
        } finally {
            this.loading = false;
        }
    },
    
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

    closePreview() {
        this.showPreview = false;
        this.previewContent = '';
    },

    // Helper untuk feedback
    setFeedback(message, type = 'success') {
        this.feedback = { message, type };
        setTimeout(() => this.feedback = null, 3000);
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

    // Fetch all notifications
    async fetchNotifications() {
        if (this.loading) return;
        
        this.loading = true;
        try {
            const path = window.location.pathname;
            let url;
            
            // Fix URL based on the new routes
            if (path.includes('/admin/')) {
                url = '/admin/information/list';
            } else if (path.includes('/pengajar/')) {
                url = '/pengajar/notifications';
            } else if (path.includes('/wali-kelas/')) {
                url = '/wali-kelas/notifications';
            }
            
            if (!url) return;

            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`Network response was not ok: ${response.status}`);
            }
            
            const data = await response.json();
            this.items = data.items || [];
            
            // Log for debugging
            console.log('Fetched notifications:', this.items);
        } catch (error) {
            console.error('Error fetching notifications:', error);
        } finally {
            this.loading = false;
        }
    },

    // Fix URL for markAsRead
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
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.ok) {
                // Update local state
                this.items = this.items.map(item => {
                    if (item.id === notificationId) {
                        return { ...item, is_read: true };
                    }
                    return item;
                });
                
                await this.fetchUnreadCount();
                return true;
            }
            
            return false;
        } catch (error) {
            console.error('Error marking notification as read:', error);
            return false;
        }
    },

    // Get unread notifications count
    async fetchUnreadCount() {
        try {
            const response = await fetch('/notifications/unread-count', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`Network response was not ok: ${response.status}`);
            }
            
            const data = await response.json();
            this.unreadCount = data.count;
            return data.count;
        } catch (error) {
            console.error('Error fetching unread count:', error);
            return 0;
        }
    },

    // Add new notification (for admin)
    async addNotification(notification) {
        try {
            const response = await fetch('/admin/information', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(notification)
            });
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to add notification');
            }
            
            const result = await response.json();
            
            if (result.success) {
                await this.fetchNotifications();
                return true;
            }
            return false;
        } catch (error) {
            console.error('Error adding notification:', error);
            return false;
        }
    },

    // Delete notification (for admin)
    async deleteNotification(id) {
        try {
            const response = await fetch(`/admin/information/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to delete notification');
            }

            const result = await response.json();
            
            if (result.success) {
                // Update local state by removing the deleted item
                this.items = this.items.filter(item => item.id !== id);
                return true;
            }
            return false;
        } catch (error) {
            console.error('Error deleting notification:', error);
            return false;
        }
    },

    // Start auto-refresh interval
    startAutoRefresh() {
        this.stopAutoRefresh(); // Stop any existing interval
        this.refreshInterval = setInterval(() => {
            this.fetchNotifications();
            this.fetchUnreadCount();
        }, 30000); // Update every 30 seconds
    },

    // Stop auto-refresh interval
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


// Add this to your JavaScript section
document.addEventListener('alpine:init', () => {
  Alpine.data('notificationHandler', () => ({
    showModal: false,
    isOpen: false,
    errorMessage: '',
    successMessage: '',
    isSubmitting: false,
    guruSearchTerm: '',
    notificationForm: {
      title: '',
      content: '',
      target: '',
      specific_users: [],
    },

    init() {
      this.$store.notification.fetchNotifications();
      this.$store.notification.fetchUnreadCount();
      this.$store.notification.startAutoRefresh();
    },

    // Enhanced timestamp formatting function
    formatTimeStamp(dateString) {
    if (!dateString) return '';
    
    try {
        const date = new Date(dateString);
        const now = new Date();
        const yesterday = new Date(now);
        yesterday.setDate(yesterday.getDate() - 1);
        
        // Check if the date is today
        const isToday = date.toDateString() === now.toDateString();
        
        // Check if the date is yesterday
        const isYesterday = date.toDateString() === yesterday.toDateString();
        
        // Check if the date is within the current year
        const isCurrentYear = date.getFullYear() === now.getFullYear();
        
        // Format based on recency
        if (isToday) {
        // Today - show time only "14:30"
        return date.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });
        } else if (isYesterday) {
        // Yesterday - show "Kemarin 14:30"
        return `Kemarin ${date.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        })}`;
        } else if (isCurrentYear) {
        // This year - show "5 Jun 14:30"
        return `${date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short'
        })} ${date.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        })}`;
        } else {
        // Different year - show "5 Jun 2023"
        return date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });
        }
    } catch (e) {
        console.error('Error formatting date:', e);
        return dateString; // Return original string if there's an error
    }
    },

    // Get target text for notification display
    getTargetText(item) {
      if (!item.target) return 'Semua';

      switch(item.target) {
        case 'all':
          return 'Semua';
        case 'guru':
          return 'Semua Guru';
        case 'wali_kelas':
          return 'Semua Wali Kelas';
        case 'specific':
          if (item.specific_users && item.specific_users.length > 0) {
            if (item.target_display) {
              return item.target_display;
            }
            return item.specific_users.length === 1 
              ? 'Guru Tertentu (1)' 
              : `${item.specific_users.length} Guru Tertentu`;
          }
          return 'Guru Tertentu';
        default:
          return 'Semua';
      }
    },

    // Helper method to ensure text always truncates properly
    ensureTruncated(text, maxLength = 50) {
      if (!text) return '';
      return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    },

    async submitNotification() {
        if (this.isSubmitting) return;
        
        // Basic validation
        if (!this.notificationForm.title.trim()) {
            this.errorMessage = 'Judul tidak boleh kosong';
            setTimeout(() => { this.errorMessage = ''; }, 3000);
            return;
        }
        
        if (!this.notificationForm.content.trim()) {
            this.errorMessage = 'Isi tidak boleh kosong';
            setTimeout(() => { this.errorMessage = ''; }, 3000);
            return;
        }
        
        // Content length validation
        if (this.notificationForm.content.length > 100) {
            this.notificationForm.content = this.notificationForm.content.substring(0, 100);
        }
        
        if (!this.notificationForm.target) {
            this.errorMessage = 'Target notifikasi harus dipilih';
            setTimeout(() => { this.errorMessage = ''; }, 3000);
            return;
        }
        
        // Validate specific users if target is specific
        if (this.notificationForm.target === 'specific' && this.notificationForm.specific_users.length === 0) {
            this.errorMessage = 'Pilih minimal satu guru untuk notifikasi';
            setTimeout(() => { this.errorMessage = ''; }, 3000);
            return;
        }

        try {
            this.isSubmitting = true;
            const result = await this.$store.notification.addNotification(this.notificationForm);
            
            if (result) {
                this.successMessage = 'Notifikasi berhasil ditambahkan';
                this.resetForm();
                this.showModal = false; // Close modal after success
                await this.$store.notification.fetchNotifications(); // Refresh notifications
            } else {
                this.errorMessage = 'Gagal menambahkan notifikasi';
            }
        } catch (error) {
            console.error('Error:', error);
            this.errorMessage = 'Terjadi kesalahan: ' + (error.message || 'Tidak dapat menambahkan notifikasi');
        } finally {
            this.isSubmitting = false;
            
            if (this.errorMessage) {
                setTimeout(() => {
                    this.errorMessage = '';
                }, 3000);
            }
            
            if (this.successMessage) {
                setTimeout(() => {
                    this.successMessage = '';
                }, 3000);
            }
        }
    },

    resetForm() {
        this.notificationForm = {
            title: '',
            content: '',
            target: '',
            specific_users: [],
        };
        this.guruSearchTerm = '';
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
});

Alpine.store('pageLoading', {
    isLoading: false,
    elementStates: {}, // Track state per element
    
    startLoading() {
        this.isLoading = true;
    },
    
    stopLoading() {
        this.isLoading = false;
    },
    
    // Untuk tracking preload komponen tertentu
    markComponentLoaded(id) {
        this.elementStates[id] = true;
    },
    
    isComponentLoaded(id) {
        return this.elementStates[id] || false;
    }
});

function ensureSidebarVisible() {
    const sidebar = document.getElementById('logo-sidebar');
    if (sidebar) {
        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('sm:translate-x-0');
    }
}

function preloadPermanentComponents() {
    const permanentElements = document.querySelectorAll('[data-turbo-permanent]');
    
    permanentElements.forEach(element => {
        const elementId = element.id;
        if (!elementId) return;
        
        if (Alpine.store('pageLoading').isComponentLoaded(elementId)) {
            return; // Komponen sudah dipreload
        }
        
        // Set opacity 1 untuk mencegah "flash"
        element.style.opacity = '1';
        
        // Tandai komponen sebagai sudah di-preload
        Alpine.store('pageLoading').markComponentLoaded(elementId);
    });
}


document.addEventListener('turbo:before-visit', () => {
    Alpine.store('pageLoading').startLoading();
});

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

        // Hapus dulu semua kelas aktif dari semua menu
        sidebarLinks.forEach(link => {
            link.classList.remove('bg-green-100', 'bg-gray-100', 'shadow-md', 'active');
            link.removeAttribute('aria-current');
        });
        
        // Cari menu yang paling spesifik dengan path saat ini
        let mostSpecificLink = null;
        let maxMatchLength = 0;

        sidebarLinks.forEach(link => {
            const path = link.dataset.path;
            if (path && currentPath.includes(path)) {
                // Temukan menu dengan path terpanjang yang cocok
                if (path.length > maxMatchLength) {
                    maxMatchLength = path.length;
                    mostSpecificLink = link;
                }
            }
        });

        // Hanya menu yang paling spesifik yang diberi highlight
        if (mostSpecificLink) {
            mostSpecificLink.classList.add('bg-gray-100', 'active');
            mostSpecificLink.setAttribute('aria-current', 'page');
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
document.addEventListener('DOMContentLoaded', preloadPermanentComponents);
document.addEventListener('turbo:load', preloadPermanentComponents);
document.addEventListener('turbo:load', ensureSidebarVisible);
document.addEventListener('turbo:render', ensureSidebarVisible);
// Cleanup listeners
document.addEventListener('turbo:before-cache', () => {
    if (window.Alpine) {
        document.querySelectorAll('[x-data]').forEach(el => {
            // Simpan status dropdown sebelum cache
            if (el.__x && el.__x.$data && el.__x.$data.openDropdown !== undefined) {
                localStorage.setItem('sidebar_dropdown_state', JSON.stringify(el.__x.$data.openDropdown));
            }
        });
    }

    const notificationHandler = document.querySelector('[x-data="notificationHandler"]');
    if (notificationHandler && notificationHandler.__x) {
        notificationHandler.__x.destroy();
    }

    const reportManager = document.querySelector('[x-data="reportManager"]');
    if (reportManager && reportManager.__x) {
        reportManager.__x.destroy();
    }

    const sidebarElements = document.querySelectorAll('#logo-sidebar');
    if (sidebarElements.length > 1) {
        for (let i = 1; i < sidebarElements.length; i++) {
            sidebarElements[i].remove();
        }
    }
    
    // Pastikan gambar tidak diload ulang dengan menyimpan status loaded
    document.querySelectorAll('#logo-sidebar img').forEach(img => {
        if (img.complete) {
            img.dataset.loaded = 'true';
        }
    });

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

window.Alpine = Alpine;
if (!window.alpineInitialized) {
    Alpine.start();
    window.alpineInitialized = true;
}