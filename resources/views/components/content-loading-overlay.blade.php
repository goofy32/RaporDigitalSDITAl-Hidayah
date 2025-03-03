<div 
    x-data
    x-show="$store.pageLoading.isLoading"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center bg-white bg-opacity-70"
    style="display: none;">
    <div class="flex flex-col items-center">
        <div class="w-16 h-16 border-4 border-green-500 border-t-transparent rounded-full animate-spin"></div>
        <p class="mt-4 text-gray-700 font-medium">Loading...</p>
    </div>
</div>