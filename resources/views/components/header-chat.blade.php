{{-- resources/views/components/header-chat.blade.php --}}
<!-- Header Chat Dropdown Component - Standalone -->
<div x-data="headerChatDropdown()" @click.away="open = false" class="relative">
    <!-- Chat Icon Button -->
    <button @click="toggleChat()" 
            class="relative p-2 rounded-full text-gray-500 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 transition-colors">
        <span class="sr-only">Open chat</span>
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
        </svg>
        <!-- Unread chat badge -->
        <span x-show="unreadCount > 0" 
              x-text="unreadCount"
              class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center animate-pulse"></span>
    </button>

    <!-- Chat Dropdown Window -->
    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="transform opacity-0 scale-95 translate-y-2"
         class="absolute right-0 z-50 mt-2 w-80 sm:w-96 origin-top-right rounded-2xl bg-white shadow-2xl border border-gray-200 flex flex-col overflow-hidden"
         style="height: 500px;">
        
        <!-- Chat Header -->
        <div class="bg-gradient-to-r from-emerald-500 to-green-600 text-white p-3 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div class="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-sm">Live Chat</h3>
                    <p class="text-xs text-emerald-100">
                        <span class="inline-block w-1.5 h-1.5 bg-green-400 rounded-full mr-1 animate-pulse"></span>
                        <span x-text="onlineUsers + ' online'"></span>
                    </p>
                </div>
            </div>
            <button @click="open = false" class="p-1 hover:bg-white/10 rounded transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Chat Messages -->
        <div class="flex-1 p-3 overflow-y-auto bg-gray-50" x-ref="messagesContainer">
            <div class="space-y-2">
                <!-- Welcome Message -->
                <div class="text-center py-2">
                    <span class="text-xs text-gray-500 bg-white px-3 py-1 rounded-full">
                        Welcome to Live Chat
                    </span>
                </div>

                <!-- Messages -->
                <template x-for="message in messages" :key="message.id">
                    <div class="flex" :class="message.isOwn ? 'justify-end' : 'justify-start'">
                        <div class="max-w-[75%]">
                            <div x-show="!message.isOwn" class="text-xs text-gray-600 mb-1" x-text="message.user.name"></div>
                            <div class="rounded-xl px-3 py-2 text-xs" 
                                 :class="message.isOwn ? 'bg-emerald-500 text-white' : 'bg-white text-gray-800 border'">
                                <p x-text="message.message"></p>
                                <div class="text-[10px] mt-1 opacity-70" x-text="message.timestamp"></div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Typing Indicator -->
                <div x-show="isTyping" class="flex justify-start">
                    <div class="bg-white border rounded-xl px-3 py-2">
                        <div class="flex space-x-1">
                            <div class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce"></div>
                            <div class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                            <div class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <template x-if="loading">
                    <div class="py-4 text-center">
                        <svg class="animate-spin h-5 w-5 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-xs text-gray-500 mt-2">Loading messages...</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- Chat Input -->
        <div class="p-3 border-t bg-white">
            <form @submit.prevent="sendMessage()" class="flex space-x-2">
                <input type="text" 
                       x-model="newMessage" 
                       placeholder="Type your message..." 
                       class="flex-1 border border-gray-300 rounded-full px-3 py-2 text-xs focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                       maxlength="500">
                <button type="submit" 
                        :disabled="!newMessage.trim() || sending"
                        class="bg-emerald-500 hover:bg-emerald-600 disabled:bg-gray-300 text-white rounded-full p-2 transition-colors duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function headerChatDropdown() {
    return {
        open: false,
        loading: false,
        messages: [],
        newMessage: '',
        sending: false,
        unreadCount: 0,
        onlineUsers: 1,
        isTyping: false,
        messageId: 1,
        currentUserId: null,
        currentUserName: null,
        refreshInterval: null,

        init() {
            // Get current user data
            this.currentUserId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
            this.currentUserName = document.querySelector('meta[name="user-name"]')?.getAttribute('content');

            // Load unread count from localStorage
            this.loadUnreadCount();

            // Listen for storage changes (from other tabs)
            window.addEventListener('storage', (e) => {
                if (e.key === 'header-chat-unread-count') {
                    this.unreadCount = parseInt(e.newValue || '0');
                }
            });

            // Listen for new messages via Echo
            if (window.Echo) {
                window.Echo.channel('chat')
                    .listen('.message.sent', (e) => {
                        // Only process if from other users
                        if (e.user.id !== this.currentUserId) {
                            // Add to messages if chat is open
                            if (this.open) {
                                this.addMessage(e.message, e.user, e.timestamp, false);
                            } else {
                                // Increment unread if closed
                                this.unreadCount++;
                                this.saveUnreadCount();
                            }
                        }
                    });
            }

            // Auto-refresh unread count every 30s
            this.refreshInterval = setInterval(() => {
                if (!this.open) {
                    this.loadUnreadCount();
                }
            }, 30000);
        },

        toggleChat() {
            this.open = !this.open;
            
            if (this.open) {
                // Reset unread when opening
                this.unreadCount = 0;
                this.saveUnreadCount();
                
                // Load chat history
                this.loadChatHistory();
            }
        },

        async loadChatHistory() {
            this.loading = true;
            try {
                const response = await fetch('{{ route("chat.messages") }}', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.messages) {
                        this.messages = [];
                        this.messageId = 1;
                        
                        data.messages.forEach(msg => {
                            this.messages.push({
                                id: this.messageId++,
                                message: msg.message,
                                user: msg.user,
                                timestamp: msg.timestamp,
                                isOwn: msg.isOwn
                            });
                        });
                        
                        this.$nextTick(() => {
                            this.scrollToBottom();
                        });
                    }
                }
            } catch (error) {
                console.error('Error loading chat history:', error);
            } finally {
                this.loading = false;
            }
        },

        loadUnreadCount() {
            this.unreadCount = parseInt(localStorage.getItem('header-chat-unread-count') || '0');
        },

        saveUnreadCount() {
            localStorage.setItem('header-chat-unread-count', this.unreadCount.toString());
        },

        async sendMessage() {
            if (!this.newMessage.trim() || this.sending) return;

            const message = this.newMessage.trim();
            this.newMessage = '';
            this.sending = true;

            try {
                const response = await fetch('{{ route("chat.send") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Add message to own chat immediately
                    this.addMessage(message, { 
                        id: this.currentUserId, 
                        name: this.currentUserName 
                    }, new Date().toLocaleTimeString('en-US', { 
                        hour: '2-digit', 
                        minute: '2-digit', 
                        hour12: false 
                    }), true);
                } else {
                    throw new Error(data.message || 'Failed to send message');
                }
            } catch (error) {
                console.error('Error sending message:', error);
                alert('Failed to send message. Please try again.');
                this.newMessage = message;
            } finally {
                this.sending = false;
            }
        },

        addMessage(message, user, timestamp, isOwn = false) {
            this.messages.push({
                id: this.messageId++,
                message,
                user,
                timestamp,
                isOwn
            });

            this.$nextTick(() => {
                this.scrollToBottom();
            });
        },

        scrollToBottom() {
            const container = this.$refs.messagesContainer;
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        },

        destroy() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
            }
        }
    }
}
</script>