<div 
    x-data="sessionTimeout"
    x-init="init()"
    x-show="isExpired && !isLoggingOut"
    class="fixed inset-0 z-50 overflow-y-auto"
    role="dialog"
    aria-modal="true"
    style="display: none;"
    data-turbo-permanent
    id="session-alert">
    
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                 @click.away="() => {}">
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
                                    Sesi Anda telah berakhir karena tidak ada aktivitas dalam 2 jam. Silakan login kembali untuk melanjutkan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button @click="handleLogout" 
                            type="button"
                            class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">
                        Login Kembali
                    </button>
                    
                    <!-- Button untuk testing - remove in production -->
                    <button @click="resetSession" 
                            type="button"
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                            style="display: none;" 
                            x-show="window.location.hostname === 'localhost'">
                        Reset (Test)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Debug info (remove in production) -->
<div x-data="sessionTimeout" 
     x-show="window.location.hostname === 'localhost'" 
     class="fixed bottom-4 right-4 bg-black bg-opacity-75 text-white p-2 rounded text-xs z-50"
     style="display: none;">
    <div>Last Activity: <span x-text="new Date(parseInt(sessionStorage.getItem('lastActivityTime') || Date.now())).toLocaleTimeString()"></span></div>
    <div>Is Expired: <span x-text="isExpired"></span></div>
    <div>Is Logging Out: <span x-text="isLoggingOut"></span></div>
    <div>Check Interval: <span x-text="checkInterval ? 'Running' : 'Stopped'"></span></div>
</div>