<div
    x-data="sessionTimeout"
    x-init="init()"
    x-show="isExpired"
    class="fixed inset-0 z-50 overflow-y-auto"
    role="dialog"
    aria-modal="true"
    style="display: none;">
    
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-base font-semibold leading-6 text-gray-900">Sesi Berakhir</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Sesi Anda telah berakhir karena tidak ada aktivitas. Silakan login kembali untuk melanjutkan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">
                            Login Kembali
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('sessionTimeout', () => ({
        isExpired: false,
        timeoutDuration: {{ config('session.lifetime', 120) * 60 * 1000 }}, // Convert minutes to milliseconds
        checkInterval: null,

        init() {
            this.setupSessionCheck();
            
            // Reset on Turbo navigation
            document.addEventListener('turbo:load', () => {
                this.setupSessionCheck();
            });

            // Cleanup on Turbo cache
            document.addEventListener('turbo:before-cache', () => {
                if (this.checkInterval) {
                    clearInterval(this.checkInterval);
                }
            });
        },

        setupSessionCheck() {
            // Clear existing interval if any
            if (this.checkInterval) {
                clearInterval(this.checkInterval);
            }

            // Initialize last activity time if not set
            if (!sessionStorage.getItem('lastActivityTime')) {
                sessionStorage.setItem('lastActivityTime', Date.now().toString());
            }

            // Update last activity time on user interaction
            const updateActivity = () => {
                if (!this.isExpired) {
                    sessionStorage.setItem('lastActivityTime', Date.now().toString());
                }
            };

            // Remove existing event listeners
            ['mousemove', 'keypress', 'click', 'touchstart'].forEach(eventName => {
                window.removeEventListener(eventName, updateActivity);
                window.addEventListener(eventName, updateActivity);
            });

            // Check session status
            const checkSession = () => {
                const lastActivity = parseInt(sessionStorage.getItem('lastActivityTime') || '0');
                const now = Date.now();
                
                if (now - lastActivity > this.timeoutDuration) {
                    this.isExpired = true;
                    clearInterval(this.checkInterval);
                }
            };

            // Start checking session status
            this.checkInterval = setInterval(checkSession, 60000); // Check every minute
            checkSession(); // Initial check
        }
    }));
});
</script>