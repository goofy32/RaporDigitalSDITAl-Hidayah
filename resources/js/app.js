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



// Event Listeners0
document.addEventListener('turbo:load', () => {
    debouncedUpdateSidebar();
    initFlowbite();
    
    // Handle dropdown state
    const dropdownButton = document.querySelector('[data-collapse-toggle="dropdown-rapor"]');
    const dropdownContent = document.getElementById('dropdown-rapor');
    
    if (dropdownButton && dropdownContent) {
        // Cek URL untuk menentukan apakah dropdown harus terbuka
        const currentPath = window.location.pathname;
        if (currentPath.includes('format-rapor')) {
            dropdownContent.classList.add('show');
            dropdownButton.classList.add('bg-green-100', 'shadow-md');
        }

        // Toggle dropdown saat diklik
        dropdownButton.addEventListener('click', () => {
            dropdownContent.classList.toggle('show');
        });
    }
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

    // Preserve dropdown state
    const dropdown = document.getElementById('dropdown-rapor');
    const isOpen = !dropdown.classList.contains('hidden');
    if (isOpen) {
        const newDropdown = event.detail.newBody.getElementById('dropdown-rapor');
        if (newDropdown) {
            newDropdown.classList.remove('hidden');
        }
    }

    window.formChanged = false;
});

document.addEventListener('turbo:before-cache', () => {
    document.querySelectorAll('#logo-sidebar a').forEach(link => {
        link.classList.remove('bg-green-100', 'shadow-md');
    });
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

document.addEventListener('turbo:before-visit', (event) => {
    if (window.formChanged) {
        if (!confirm('Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?')) {
            event.preventDefault();
        }
    }
});

document.addEventListener('turbo:visit', () => {
    debouncedUpdateSidebar();
});

window.Alpine = Alpine;
Alpine.start();