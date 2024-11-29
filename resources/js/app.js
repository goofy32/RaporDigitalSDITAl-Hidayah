import './bootstrap';
import 'flowbite';
import '@hotwired/turbo';
import Alpine from 'alpinejs';

// Daftarkan komponen Alpine sebelum menginisialisasi
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

// Setup Turbo
document.addEventListener('turbo:before-render', (event) => {
    // Preserve permanent elements
    ['topbar', 'sidebar', 'session-alert'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            const clone = element.cloneNode(true);
            event.detail.newBody.querySelector(`#${id}`)?.replaceWith(clone);
        }
    });
});

document.addEventListener('turbo:load', () => {
    // Initialize Flowbite components
    initFlowbite();
});

// Handle session expiry
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

document.addEventListener('turbo:before-visit', (event) => {
    if (window.formChanged) {
        if (!confirm('Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?')) {
            event.preventDefault();
        }
    }
});

document.addEventListener('turbo:before-render', () => {
    window.formChanged = false;
});

document.addEventListener('turbo:load', () => {
    initFlowbite();
});

window.Alpine = Alpine;
Alpine.start();