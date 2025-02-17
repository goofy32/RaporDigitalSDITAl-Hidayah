class FormProtection {
    constructor(formSelector = 'form', options = {}) {
        this.form = document.querySelector(formSelector);
        this.formChanged = false;
        this.options = {
            confirmMessage: 'Anda memiliki perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?',
            backButtonSelector: 'button[onclick="window.history.back()"], .back-btn',
            excludeSelectors: [], // Array of selectors to exclude from change monitoring
            ...options
        };
        
        if (this.form) {
            this.init();
        }
    }

    init() {
        this.setupChangeListeners();
        this.setupBackButtonListener();
        this.setupBeforeUnloadListener();
    }

    setupChangeListeners() {
        // Monitor all form inputs
        this.form.querySelectorAll('input, select, textarea').forEach(element => {
            if (!this.shouldExcludeElement(element)) {
                element.addEventListener('change', () => this.formChanged = true);
                element.addEventListener('keyup', () => this.formChanged = true);
            }
        });

        // Monitor buttons that might modify form state
        this.form.querySelectorAll('button[type="button"]').forEach(button => {
            if (!this.shouldExcludeElement(button)) {
                button.addEventListener('click', () => this.formChanged = true);
            }
        });
    }

    setupBackButtonListener() {
        document.querySelectorAll(this.options.backButtonSelector).forEach(button => {
            button.addEventListener('click', (e) => {
                if (this.formChanged) {
                    e.preventDefault();
                    if (confirm(this.options.confirmMessage)) {
                        window.history.back();
                    }
                }
            });
        });
    }

    setupBeforeUnloadListener() {
        window.addEventListener('beforeunload', (e) => {
            if (this.formChanged) {
                e.preventDefault();
                e.returnValue = this.options.confirmMessage;
                return e.returnValue;
            }
        });
    }

    shouldExcludeElement(element) {
        return this.options.excludeSelectors.some(selector => 
            element.matches(selector)
        );
    }

    resetFormState() {
        this.formChanged = false;
    }

    markAsChanged() {
        this.formChanged = true;
    }
}

// Export untuk penggunaan dengan module
export default FormProtection;