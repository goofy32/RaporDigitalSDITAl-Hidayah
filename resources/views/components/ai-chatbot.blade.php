<div x-data="geminiChatDebug" class="fixed bottom-4 right-4 sm:right-4 sm:left-auto z-50 chatbot-container" x-cloak>
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
        class="absolute bottom-16 right-0 w-96 sm:w-96 max-w-[calc(100vw-2rem)] bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden chatbot-window"
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
                    <h3 class="font-semibold text-lg">AI Nilai Assistant</h3>
                    <p class="text-green-100 text-xs">Analisis Nilai Akademik</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-2">
                <!-- History Menu Button -->
                <div class="relative" x-data="{ showMenu: false }">
                    <button @click="showMenu = !showMenu" 
                            class="text-white hover:text-green-200 transition-colors p-1 rounded-full hover:bg-white hover:bg-opacity-10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div x-show="showMenu" 
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        @click.away="showMenu = false"
                        class="absolute right-0 top-8 w-48 bg-white rounded-lg shadow-xl z-10 py-2">
                        
                        <button @click="handleClearHistory(); showMenu = false" 
                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Hapus Semua Riwayat
                        </button>
                    </div>
                </div>
                
                <!-- Close Button -->
                <button @click="isOpen = false" 
                        class="text-white hover:text-green-200 transition-colors p-1 rounded-full hover:bg-white hover:bg-opacity-10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Chat Messages -->
        <div x-ref="chatContainer" class="flex-1 p-4 overflow-y-auto" style="height: 20rem; max-height: 20rem;">
            <template x-for="(chat, index) in chats" :key="chat.created_at + index">
                <div class="mb-4 group">
                    <!-- User Message -->
                    <div class="flex justify-end mb-2">
                        <div class="bg-green-600 text-white px-4 py-2 rounded-xl rounded-br-md max-w-xs lg:max-w-sm shadow-sm relative">
                            <p class="text-sm" x-text="chat.message"></p>
                            <!-- Delete button for user message -->
                            <button @click="handleDeleteChat(index)" 
                                    class="absolute -top-2 -left-2 w-5 h-5 bg-red-500 text-white rounded-full text-xs opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600">
                                <svg class="w-3 h-3 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
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
            <!-- <div x-show="isLoading" class="flex justify-start mb-4">
                <div class="bg-gray-100 px-4 py-2 rounded-xl rounded-bl-md shadow-sm">
                    <div class="flex items-center text-gray-500">
                        <svg class="animate-spin h-4 w-4 mr-2" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm">AI sedang menganalisis nilai...</span>
                    </div>
                </div>
            </div> -->

            <!-- Empty state -->
            <div x-show="chats.length === 0 && !isLoading" class="text-center text-gray-500 py-8">
                <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <p class="text-sm">Mulai analisis nilai akademik</p>
                <p class="text-xs text-gray-400 mt-1">Tanyakan apapun terkait nilai dan cara menggunakan web ini</p>
            </div>
        </div>

        <!-- Suggestions -->
        <!-- <div x-show="showSuggestions && suggestions.length > 0" class="px-4 pb-3 border-t bg-gray-50">
            <div class="text-xs text-gray-500 mb-2 font-medium">💡 Analisis yang tersedia:</div>
            <div class="flex flex-wrap gap-2">
                <template x-for="suggestion in suggestions.slice(0, 3)">
                    <button @click="useSuggestion(suggestion)" 
                            class="text-xs bg-white hover:bg-green-50 border border-gray-200 hover:border-green-300 px-3 py-1.5 rounded-full text-gray-700 hover:text-green-700 transition-colors">
                        <span x-text="suggestion.length > 35 ? suggestion.substring(0, 35) + '...' : suggestion"></span>
                    </button>
                </template>
            </div>
        </div> -->

        <!-- Input Form -->
        <div class="p-4 border-t bg-white">
            <form @submit.prevent="sendMessage()" class="flex space-x-2">
                <div class="flex-1 relative">
                    <input type="text" 
                        x-model="message"
                        placeholder="Tanyakan tentang nilai akademik..."
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
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 718-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
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
        /* Container chatbot */
        [x-data="geminiChatDebug"] {
            position: fixed !important;
            bottom: 0 !important;
            right: 0 !important;
            left: 0 !important;
            width: 100% !important;
            z-index: 9999 !important;
        }
        
        /* Button toggle tetap di pojok */
        [x-data="geminiChatDebug"] > button {
            position: fixed !important;
            bottom: 1rem !important;
            right: 1rem !important;
            z-index: 10000 !important;
        }
        
        /* Chat window full screen bottom di mobile */
        [x-data="geminiChatDebug"] .w-96 {
            width: 100% !important;
            max-width: none !important;
            right: 0 !important;
            left: 0 !important;
            bottom: 0 !important;
            position: fixed !important;
            max-height: 70vh !important;
            border-radius: 1rem 1rem 0 0 !important;
        }
        
        /* Chat messages lebih pendek untuk ruang input */
        [x-data="geminiChatDebug"] .overflow-y-auto {
            max-height: calc(70vh - 8rem) !important;
            height: auto !important;
        }
        
        /* Input form sticky di bottom */
        [x-data="geminiChatDebug"] .border-t {
            position: sticky !important;
            bottom: 0 !important;
            background: white !important;
            z-index: 10 !important;
        }
    }
</style>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('geminiChatDebug', () => ({
        isOpen: false,
        message: '',
        chats: [],
        isLoading: false,
        error: '',
        showSuggestions: true,
        suggestions: [],
        showHistoryMenu: false,
        
        init() {
            console.log('Alpine chat component initialized');
            this.suggestions = this.getUserRoleBasedSuggestions();
            this.loadHistory();
            this.$watch('message', (value) => {
                this.showSuggestions = value.length === 0;
            });
        },
        
        getUserRoleBasedSuggestions() {
            const currentPath = window.location.pathname;
            
            if (currentPath.startsWith('/admin')) {
                return [
                    'Berikan overview nilai akademik seluruh sekolah',
                    'Siswa mana yang memerlukan perhatian khusus?',
                    'Mata pelajaran mana yang paling sulit bagi siswa?',
                    'Bagaimana perbandingan performa antar kelas?',
                    'Progress input nilai seluruh sekolah'
                ];
            } else if (currentPath.startsWith('/pengajar')) {
                return [
                    'Analisis nilai mata pelajaran yang saya ajar',
                    'Siswa mana yang nilainya di bawah KKM?',
                    'Siswa mana yang belum saya isi nilainya?',
                    'Berapa persen progress input nilai saya?',
                    'Bagaimana trend nilai di kelas saya?',
                    'Mata pelajaran mana yang belum selesai saya nilai?'
                ];
            } else if (currentPath.startsWith('/wali-kelas')) {
                return [
                    'Ringkasan performa akademik kelas saya',
                    'Siswa mana yang perlu bimbingan tambahan?',
                    'Status kelengkapan nilai di kelas saya?',
                    'Guru mana yang belum selesai input nilai di kelas saya?',
                    'Mata pelajaran apa yang perlu difokuskan di kelas?',
                    'Progress input nilai per mata pelajaran di kelas saya?'
                ];
            }
            
            return [
                'Bagaimana cara menggunakan sistem ini?',
                'Apa yang bisa saya tanyakan tentang nilai?'
            ];
        },
        
        getApiEndpoint() {
            const currentPath = window.location.pathname;
            
            if (currentPath.startsWith('/admin')) {
                return '/admin/gemini/send-message';
            } else if (currentPath.startsWith('/pengajar')) {
                return '/pengajar/gemini/send-message';
            } else if (currentPath.startsWith('/wali-kelas')) {
                return '/wali-kelas/gemini/send-message';
            }
            
            return '/admin/gemini/send-message';
        },
        
        toggleChat() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.$nextTick(() => {
                    this.scrollToBottom();
                });
            }
        },
        
        // Wrapper methods to fix scope issues
        handleClearHistory() {
            console.log('Clear history called');
            this.clearAllHistory();
        },
        
        handleDeleteChat(index) {
            console.log('Delete chat called for index:', index);
            this.deleteSpecificChat(index);
        },
        
        async clearAllHistory() {
            console.log('clearAllHistory method called');
            
            if (!confirm('Apakah Anda yakin ingin menghapus semua riwayat chat? Tindakan ini tidak dapat dibatalkan.')) {
                return;
            }
            
            try {
                const apiEndpoint = this.getApiEndpoint().replace('/send-message', '/clear-history');
                
                const response = await fetch(apiEndpoint, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.chats = [];
                    this.showHistoryMenu = false;
                    this.showSuggestions = true;
                    console.log('History cleared successfully');
                } else {
                    alert('Gagal menghapus riwayat: ' + data.message);
                }
                
            } catch (error) {
                console.error('Error clearing history:', error);
                alert('Terjadi kesalahan saat menghapus riwayat');
            }
        },
        
        async deleteSpecificChat(chatIndex) {
            console.log('deleteSpecificChat method called for index:', chatIndex);
            
            if (!confirm('Hapus chat ini?')) {
                return;
            }
            
            try {
                const chat = this.chats[chatIndex];
                if (!chat.id) {
                    this.chats.splice(chatIndex, 1);
                    return;
                }
                
                const apiEndpoint = this.getApiEndpoint().replace('/send-message', '/chat/' + chat.id);
                
                const response = await fetch(apiEndpoint, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.chats.splice(chatIndex, 1);
                    console.log('Chat deleted successfully');
                } else {
                    alert('Gagal menghapus chat: ' + data.message);
                }
                
            } catch (error) {
                console.error('Error deleting chat:', error);
                alert('Terjadi kesalahan saat menghapus chat');
            }
        },
        
        async sendMessage() {
            if (!this.message.trim() || this.isLoading) return;
            
            const userMessage = this.message.trim();
            this.message = '';
            this.error = '';
            this.isLoading = true;
            this.showSuggestions = false;
            
            this.chats.push({
                message: userMessage,
                response: 'AI sedang memproses... Mohon tunggu sebentar.',
                created_at: new Date().toISOString(),
                is_sending: true
            });
            
            this.scrollToBottom();
            
            try {
                const apiEndpoint = this.getApiEndpoint();
                
                const response = await fetch(apiEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        message: userMessage
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Update response dengan hasil akhir
                    this.chats[this.chats.length - 1].response = data.response;
                    this.chats[this.chats.length - 1].is_sending = false;
                    this.chats[this.chats.length - 1].id = data.chat?.id;
                    
                    // Tambah info model yang digunakan jika fallback
                    if (data.fallback) {
                        this.chats[this.chats.length - 1].response += "\n\n*Catatan: Response dari sistem fallback*";
                    }
                } else {
                    this.chats[this.chats.length - 1].response = data.message || 'Terjadi kesalahan saat memproses permintaan';
                    this.chats[this.chats.length - 1].is_error = true;
                    this.chats[this.chats.length - 1].is_sending = false;
                }
                
            } catch (error) {
                console.error('Chat error:', error);
                this.chats[this.chats.length - 1].response = 'Koneksi gagal. Silakan coba lagi dalam beberapa menit.';
                this.chats[this.chats.length - 1].is_error = true;
                this.chats[this.chats.length - 1].is_sending = false;
            } finally {
                this.isLoading = false;
                this.scrollToBottom();
            }
        },
        
        useSuggestion(suggestion) {
            this.message = suggestion;
            this.showSuggestions = false;
            this.sendMessage();
        },
        
        async loadHistory() {
            try {
                const apiEndpoint = this.getApiEndpoint().replace('/send-message', '/history');
                const response = await fetch(apiEndpoint);
                const data = await response.json();
                
                if (data.success) {
                    this.chats = data.chats.reverse();
                }
            } catch (error) {
                console.error('Failed to load chat history:', error);
            }
        },
        
        formatResponse(response) {
            if (!response) return '';
            
            let formatted = response.replace(/\n/g, '<br>');
            formatted = formatted.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            formatted = formatted.replace(/\*(.*?)\*/g, '<em>$1</em>');
            
            return formatted;
        },
        
        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.chatContainer;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        },
        
        toggleHistoryMenu() {
            this.showHistoryMenu = !this.showHistoryMenu;
        },

        isMobile() {
            return window.innerWidth <= 640;
        },

        toggleChat() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.$nextTick(() => {
                    // Auto scroll ke bottom dan focus input di mobile
                    if (window.innerWidth <= 640) {
                        setTimeout(() => {
                            const inputElement = this.$el.querySelector('input[type="text"]');
                            if (inputElement) {
                                inputElement.scrollIntoView({ 
                                    behavior: 'smooth', 
                                    block: 'center' 
                                });
                                // Focus setelah scroll selesai
                                setTimeout(() => {
                                    inputElement.focus();
                                }, 500);
                            }
                        }, 300);
                    }
                    this.scrollToBottom();
                });
            }
        }
    }))
});
</script>