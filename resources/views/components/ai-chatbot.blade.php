<div x-data="geminiChat" class="fixed bottom-4 right-4 z-50" x-cloak>
    <!-- Chat Toggle Button -->
    <button @click="toggleChat()" 
            class="bg-green-600 hover:bg-green-700 text-white p-3 rounded-full shadow-xl transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-green-300">
        <svg x-show="!isOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.955 8.955 0 01-4.126-.98L3 21l1.98-5.874A8.955 8.955 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z"></path>
        </svg>
        <svg x-show="isOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>

    <!-- Chat Window -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 transform translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 transform translate-y-4"
         class="absolute bottom-16 right-0 w-96 max-w-[calc(100vw-2rem)] bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden"
         style="display: none; max-height: 32rem;">
        
        <!-- Chat Header -->
        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-green-600 to-green-700 text-white">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-lg">AI Assistant</h3>
                    <p class="text-green-100 text-xs">Siap membantu Anda</p>
                </div>
            </div>
            <button @click="isOpen = false" 
                    class="text-white hover:text-green-200 transition-colors p-1 rounded-full hover:bg-white hover:bg-opacity-10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Chat Messages -->
        <div x-ref="chatContainer" class="flex-1 p-4 overflow-y-auto" style="height: 20rem; max-height: 20rem;">
            <template x-for="chat in chats" :key="chat.created_at">
                <div class="mb-4">
                    <!-- User Message -->
                    <div class="flex justify-end mb-2">
                        <div class="bg-green-600 text-white px-4 py-2 rounded-xl rounded-br-md max-w-xs lg:max-w-sm shadow-sm">
                            <p class="text-sm" x-text="chat.message"></p>
                        </div>
                    </div>
                    
                    <!-- AI Response -->
                    <div class="flex justify-start">
                        <div class="bg-gray-100 text-gray-800 px-4 py-2 rounded-xl rounded-bl-md max-w-xs lg:max-w-sm shadow-sm"
                            :class="{ 'text-red-600 bg-red-50': chat.is_error, 'italic text-gray-500': chat.is_sending }">
                            <p class="text-sm" x-html="formatResponse(chat.response)"></p>
                        </div>
                    </div>
                </div>
            </template>
            
            <!-- Loading indicator -->
            <div x-show="isLoading" class="flex justify-start mb-4">
                <div class="bg-gray-100 px-4 py-2 rounded-xl rounded-bl-md shadow-sm">
                    <div class="flex items-center text-gray-500">
                        <svg class="animate-spin h-4 w-4 mr-2" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm">AI sedang mengetik...</span>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div x-show="chats.length === 0 && !isLoading" class="text-center text-gray-500 py-8">
                <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.955 8.955 0 01-4.126-.98L3 21l1.98-5.874A8.955 8.955 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z"></path>
                </svg>
                <p class="text-sm">Mulai percakapan dengan AI Assistant</p>
                <p class="text-xs text-gray-400 mt-1">Tanyakan apapun tentang sistem rapor digital</p>
            </div>
        </div>

        <!-- Suggestions -->
        <div x-show="showSuggestions && suggestions.length > 0" class="px-4 pb-3 border-t bg-gray-50">
            <div class="text-xs text-gray-500 mb-2 font-medium">💡 Saran pertanyaan:</div>
            <div class="flex flex-wrap gap-2">
                <template x-for="suggestion in suggestions.slice(0, 3)">
                    <button @click="useSuggestion(suggestion)" 
                            class="text-xs bg-white hover:bg-green-50 border border-gray-200 hover:border-green-300 px-3 py-1.5 rounded-full text-gray-700 hover:text-green-700 transition-colors">
                        <span x-text="suggestion.length > 30 ? suggestion.substring(0, 30) + '...' : suggestion"></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Input Form -->
        <div class="p-4 border-t bg-white">
            <form @submit.prevent="handleFormSubmit($event)" class="flex space-x-2">
                <div class="flex-1 relative">
                    <input type="text" 
                        x-model="message"
                        placeholder="Ketik pesan Anda..."
                        :disabled="isLoading"
                        class="w-full px-4 py-2.5 pr-12 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500 text-sm"
                        maxlength="500">
                    
                    <!-- Character counter -->
                    <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-xs text-gray-400"
                         x-text="message.length + '/500'"></div>
                </div>
                
                <button type="submit" 
                        :disabled="isLoading || !message.trim()"
                        class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-4 py-2.5 rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <svg x-show="!isLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    
                    <svg x-show="isLoading" class="w-5 h-5 animate-spin" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </form>
            
            <!-- Error Message -->
            <div x-show="error" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-red-700 text-sm font-medium" x-text="error"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile responsive styles -->
<style>
    @media (max-width: 640px) {
        [x-data="geminiChat"] .w-96 {
            width: calc(100vw - 2rem) !important;
            right: 1rem !important;
            left: 1rem !important;
        }
    }
</style>