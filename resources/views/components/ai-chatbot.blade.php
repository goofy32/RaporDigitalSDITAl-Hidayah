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
                            class="text-white hover:text-green-200 transition-colors p-1 rounded-full hover:bg-white hover:bg-opacity-10"
                            title="Menu">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
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
                        class="absolute right-0 top-8 w-56 bg-white rounded-lg shadow-xl z-10 py-2">
                        
                        <!-- Reset Conversation Button -->
                        <button @click="handleResetConversation(); showMenu = false" 
                                class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Reset Konteks Percakapan
                        </button>
                        
                        <!-- Divider -->
                        <div class="border-t border-gray-100 my-1"></div>
                        
                        <!-- Clear History Button -->
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
                            :class="{ 
                                'text-red-600 bg-red-50': chat.is_error, 
                                'italic text-gray-500': chat.is_sending,
                                'system-message': chat.is_system 
                            }">
                            <p class="text-sm" x-html="formatResponse(chat.response)"></p>
                        </div>
                    </div>
                </div>
            </template>
            
            <div x-show="isLoading" class="flex justify-start mb-4">
                <div class="bg-gray-100 px-4 py-2 rounded-xl rounded-bl-md shadow-sm">
                    <div class="flex items-center text-gray-500">
                        <svg class="animate-spin h-4 w-4 mr-2" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm">
                            (<span x-text="loadingCounter" class="font-mono font-bold text-green-600"></span>) 
                            <span x-text="loadingMessage"></span>
                        </span>
                    </div>
                </div>
            </div>
            <!-- Empty state -->
            <div x-show="chats.length === 0 && !isLoading" class="space-y-4">
                <!-- Welcome message -->
                <div class="bg-gray-100 text-gray-800 px-4 py-3 rounded-xl rounded-bl-md shadow-sm">
                    <div class="flex items-start space-x-2">
                        <div class="w-6 h-6 bg-green-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-800">Halo!</p>
                            <p class="text-xs text-gray-600 mt-1">Saya AI Assistant untuk analisis nilai akademik SDIT Al-Hidayah. Saya siap membantu Anda dengan berbagai kebutuhan terkait pengelolaan nilai dan sistem rapor.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Quick suggestions -->
                <div class="space-y-2">
                    <p class="text-xs text-gray-500 font-medium px-1">Mulai dengan pertanyaan ini:</p>
                    <div class="space-y-1">
                        <button @click="useSuggestion('Apa yang bisa anda lakukan?')" 
                                class="w-full text-left bg-white hover:bg-green-50 border border-gray-200 hover:border-green-300 px-3 py-2 rounded-lg text-sm text-gray-700 hover:text-green-700 transition-colors">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Apa yang bisa anda lakukan?
                            </span>
                        </button>
                        
                        <template x-for="suggestion in getQuickSuggestions().slice(0, 2)" :key="suggestion">
                            <button @click="useSuggestion(suggestion)" 
                                    class="w-full text-left bg-white hover:bg-green-50 border border-gray-200 hover:border-green-300 px-3 py-2 rounded-lg text-sm text-gray-700 hover:text-green-700 transition-colors">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    <span x-text="suggestion.length > 35 ? suggestion.substring(0, 35) + '...' : suggestion"></span>
                                </span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Suggestions -->
        <!-- <div x-show="showSuggestions && suggestions.length > 0" class="px-4 pb-3 border-t bg-gray-50">
            <div class="text-xs text-gray-500 mb-2 font-medium">ðŸ’¡ Analisis yang tersedia:</div>
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
.loading-counter {
    font-family: 'Courier New', monospace;
    font-weight: bold;
    color: #22c55e;
    font-size: 0.9rem;
    text-shadow: 0 0 2px rgba(34, 197, 94, 0.3);
}

/* Animasi pulse untuk angka */
@keyframes counterPulse {
    0%, 100% { 
        transform: scale(1);
        opacity: 1;
    }
    50% { 
        transform: scale(1.1);
        opacity: 0.8;
    }
}

/* Loading message dengan animasi typing */
.loading-message {
    position: relative;
    overflow: hidden;
}

.loading-message::after {
    content: '...';
    animation: typingDots 1.5s infinite;
}

@keyframes typingDots {
    0%, 20% { content: ''; }
    40% { content: '.'; }
    60% { content: '..'; }
    80%, 100% { content: '...'; }
}

/* Enhanced loading container */
.enhanced-loading {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    border-left: 3px solid #22c55e;
    position: relative;
}

.enhanced-loading::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 3px;
    height: 100%;
    background: linear-gradient(to bottom, #22c55e, #16a34a);
    animation: loadingBar 2s ease-in-out infinite;
}

@keyframes loadingBar {
    0% { transform: scaleY(0.3); }
    50% { transform: scaleY(1); }
    100% { transform: scaleY(0.3); }
}

.system-message {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border-left: 3px solid #3b82f6;
    font-style: italic;
}

.system-message .text-sm {
    color: #1e40af !important;
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
        loadingCounter: 0,
        loadingMessage: 'AI sedang menganalisis nilai...',
        loadingInterval: null,
        loadingMessages: [
            'AI sedang menganalisis nilai...',
            'Memproses data akademik...',
            'Menghitung statistik...',
            'Menganalisis performa siswa...',
            'Menyiapkan rekomendasi...',
            'Mengkompilasi laporan...',
            'Memvalidasi hasil analisis...',
            'Finalisasi respons...'
        ],
        
        init() {
            console.log('Alpine chat component initialized');
            this.suggestions = this.getUserRoleBasedSuggestions();
            this.loadHistory();
            this.$watch('message', (value) => {
                this.showSuggestions = value.length === 0;
            });
        },
        
        startLoadingCounter() {
            this.loadingCounter = 1;
            this.loadingMessage = this.loadingMessages[0];
            let messageIndex = 0;
            
            this.loadingInterval = setInterval(() => {
                this.loadingCounter++;
                
                // Ganti pesan setiap 3 detik
                if (this.loadingCounter % 3 === 0) {
                    messageIndex = (messageIndex + 1) % this.loadingMessages.length;
                    this.loadingMessage = this.loadingMessages[messageIndex];
                }
                
                // Tambahkan variasi berdasarkan waktu
                if (this.loadingCounter > 15) {
                    this.loadingMessage = 'Memproses analisis kompleks...';
                }
                if (this.loadingCounter > 25) {
                    this.loadingMessage = 'AI sedang berpikir keras...';
                }
                if (this.loadingCounter > 35) {
                    this.loadingMessage = 'Hampir selesai...';
                }
            }, 1000); // Update setiap detik
        },
        
        stopLoadingCounter() {
            if (this.loadingInterval) {
                clearInterval(this.loadingInterval);
                this.loadingInterval = null;
            }
            this.loadingCounter = 0;
        },
        
        getUserRoleBasedSuggestions() {
            const currentPath = window.location.pathname;
            
            if (currentPath.startsWith('/admin')) {
                return [
                    'Berikan overview lengkap sistem akademik',
                    'Siswa mana yang belum diisi nilainya?',
                    'Mata pelajaran apa yang paling sulit bagi siswa?',
                    'Guru mana yang belum menyelesaikan input nilai?',
                    'Berapa rata-rata nilai akademik keseluruhan?',
                    'Bagaimana progress kesiapan rapor seluruh sekolah?',
                    'Kelas mana yang performanya paling baik?',
                    'Analisis nilai tertinggi dan terendah',
                    'Guru mana yang sudah selesai input nilai?',
                    'Berapa persen kelengkapan data akademik?'
                ];
            } else if (currentPath.startsWith('/pengajar')) {
                return [
                    'Analisis mata pelajaran yang saya ajar',
                    'Siswa mana yang nilainya masih di bawah KKM?',
                    'Progress input nilai saya berapa persen?',
                    'Siswa mana yang belum saya isi nilainya?',
                    'Bagaimana performa kelas yang saya ajar?',
                    'Mata pelajaran mana yang paling sulit untuk siswa?',
                    'Siapa siswa terbaik di mata pelajaran saya?',
                    'Berapa rata-rata nilai mata pelajaran saya?',
                    'Trend perkembangan nilai siswa',
                    'Rekomendasi untuk meningkatkan hasil belajar'
                ];
            } else if (currentPath.startsWith('/wali-kelas')) {
                return [
                    'Overview lengkap performa kelas saya',
                    'Siswa mana yang perlu bimbingan khusus?',
                    'Progress kelengkapan nilai di kelas saya',
                    'Guru mana yang belum input nilai di kelas saya?',
                    'Berapa siswa yang sudah siap rapor?',
                    'Mata pelajaran apa yang perlu fokus tambahan?',
                    'Perbandingan kelas saya dengan kelas lain',
                    'Siswa mana yang paling berprestasi di kelas?',
                    'Analisis kesiapan data rapor kelas saya',
                    'Rekomendasi untuk meningkatkan performa kelas'
                ];
            }
            
            return [
                'Bagaimana cara menggunakan sistem ini?',
                'Apa yang bisa saya tanyakan tentang nilai?',
                'Panduan lengkap sistem rapor'
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
        
        handleResetConversation() {
            console.log('Reset conversation called');
            this.resetConversation();
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
            
            // Start countdown timer
            this.startLoadingCounter();
            
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
                this.stopLoadingCounter(); // Stop countdown timer
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

        async resetConversation() {
            if (!confirm('Reset seluruh konteks percakapan? AI akan kehilangan memori tentang percakapan sebelumnya dan memulai dari awal.')) {
                return;
            }
            
            try {
                const apiEndpoint = this.getApiEndpoint().replace('/send-message', '/clear-history'); // Gunakan clear-history untuk sementara
                
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
                    this.showSuggestions = true;
                    this.showHistoryMenu = false;
                    
                    // Tampilkan pesan sistem bahwa konteks telah direset
                    setTimeout(() => {
                        this.chats.push({
                            message: '🔄 Konteks percakapan direset',
                            response: 'Konteks percakapan telah direset. Saya siap memulai percakapan baru dengan konteks yang segar. Silakan tanyakan apa yang Anda butuhkan!',
                            created_at: new Date().toISOString(),
                            is_system: true,
                            id: 'system_' + Date.now()
                        });
                        this.scrollToBottom();
                    }, 300);
                    
                    console.log('Conversation context reset successfully');
                } else {
                    alert('Gagal mereset konteks: ' + data.message);
                }
                
            } catch (error) {
                console.error('Error resetting conversation:', error);
                alert('Terjadi kesalahan saat mereset konteks percakapan');
            }
        },

        getQuickSuggestions() {
            const currentPath = window.location.pathname;
            
            // Jika ada chat history, berikan suggestions yang berbeda
            if (this.chats.length > 0) {
                const lastChat = this.chats[this.chats.length - 1];
                
                // Suggestions berdasarkan chat terakhir
                if (lastChat.message.toLowerCase().includes('nilai')) {
                    return [
                        'Lanjutkan analisis yang lebih detail',
                        'Bagaimana cara meningkatkan nilai tersebut?',
                        'Bandingkan dengan periode sebelumnya'
                    ];
                } else if (lastChat.message.toLowerCase().includes('siswa')) {
                    return [
                        'Analisis siswa lainnya yang serupa',
                        'Rekomendasi tindak lanjut untuk siswa ini',
                        'Perbandingan dengan siswa terbaik'
                    ];
                } else if (lastChat.message.toLowerCase().includes('guru')) {
                    return [
                        'Progress guru lainnya',
                        'Analisis efektivitas mengajar',
                        'Rekomendasi improvement untuk guru'
                    ];
                }
            }
            
            // Default suggestions berdasarkan role
            if (currentPath.startsWith('/admin')) {
                return [
                    'Berikan overview lengkap sistem akademik',
                    'Siswa mana yang belum diisi nilainya?',
                    'Mata pelajaran apa yang paling sulit bagi siswa?',
                    'Guru mana yang belum menyelesaikan input nilai?',
                    'Berapa rata-rata nilai akademik keseluruhan?',
                    'Bagaimana progress kesiapan rapor seluruh sekolah?',
                    'Kelas mana yang performanya paling baik?',
                    'Analisis nilai tertinggi dan terendah',
                    'Guru mana yang sudah selesai input nilai?',
                    'Berapa persen kelengkapan data akademik?'
                ];
            } else if (currentPath.startsWith('/pengajar')) {
                return [
                    'Analisis mata pelajaran yang saya ajar',
                    'Siswa mana yang nilainya masih di bawah KKM?',
                    'Progress input nilai saya berapa persen?',
                    'Siswa mana yang belum saya isi nilainya?',
                    'Bagaimana performa kelas yang saya ajar?',
                    'Mata pelajaran mana yang paling sulit untuk siswa?',
                    'Siapa siswa terbaik di mata pelajaran saya?',
                    'Berapa rata-rata nilai mata pelajaran saya?',
                    'Trend perkembangan nilai siswa',
                    'Rekomendasi untuk meningkatkan hasil belajar'
                ];
            } else if (currentPath.startsWith('/wali-kelas')) {
                return [
                    'Overview lengkap performa kelas saya',
                    'Siswa mana yang perlu bimbingan khusus?',
                    'Progress kelengkapan nilai di kelas saya',
                    'Guru mana yang belum input nilai di kelas saya?',
                    'Berapa siswa yang sudah siap rapor?',
                    'Mata pelajaran apa yang perlu fokus tambahan?',
                    'Perbandingan kelas saya dengan kelas lain',
                    'Siswa mana yang paling berprestasi di kelas?',
                    'Analisis kesiapan data rapor kelas saya',
                    'Rekomendasi untuk meningkatkan performa kelas'
                ];
            }
            
            return [
                'Bagaimana cara menggunakan sistem ini?',
                'Apa yang bisa saya tanyakan tentang nilai?',
                'Panduan lengkap sistem rapor'
            ];
        }
    }))
});
</script>