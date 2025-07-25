<!-- Live Chat Component -->
<div x-data="liveChat()" class="fixed bottom-6 right-6 z-50">
    <!-- Chat Toggle Button -->
    <button @click="toggleChat()" 
            class="bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white rounded-full p-4 shadow-lg hover:shadow-xl transition-all duration-300 relative">
        <!-- Chat Icon -->
        <div x-show="!isOpen" class="w-6 h-6">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
        </div>
        <!-- Close Icon -->
        <div x-show="isOpen" class="w-6 h-6" x-cloak>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </div>
        <!-- Notification Badge -->
        <span x-show="unreadCount > 0" x-text="unreadCount" x-cloak
              class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center font-bold animate-pulse">
        </span>
    </button>

    <!-- Chat Window -->
    <div x-show="isOpen" x-cloak 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 scale-75 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-75 translate-y-4"
         class="fixed bottom-24 right-6 bg-white rounded-2xl shadow-2xl border border-gray-200 flex flex-col overflow-hidden
                w-80 h-96 sm:w-96 sm:h-[500px] lg:w-[540px] lg:h-[668px]">
        
        <!-- Chat Header -->
        <div class="bg-gradient-to-r from-emerald-500 to-green-600 text-white p-3 lg:p-4 flex items-center justify-between">
            <div class="flex items-center space-x-2 lg:space-x-3">
                <div class="w-6 h-6 lg:w-8 lg:h-8 bg-white/20 rounded-full flex items-center justify-center">
                    <svg class="w-3 h-3 lg:w-4 lg:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a2 2 0 01-2-2v-2M5 8h2V5a2 2 0 012-2h6a2 2 0 012 2v3"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-xs lg:text-sm">Live Chat Tebu</h3>
                    <p class="text-xs lg:text-xs text-emerald-100">
                        <span class="inline-block w-1.5 h-1.5 lg:w-2 lg:h-2 bg-green-400 rounded-full mr-1 animate-pulse"></span>
                        <span x-text="onlineUsers + ' online'"></span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Chat Messages -->
        <div class="flex-1 p-3 lg:p-4 overflow-y-auto bg-gray-50" x-ref="messagesContainer">
            <div class="space-y-2 lg:space-y-3">
                <!-- Welcome Message -->
                <div class="text-center py-2">
                    <span class="text-xs text-gray-500 bg-white px-2 lg:px-3 py-1 rounded-full">
                        Welcome to Tebu Live Chat
                    </span>
                </div>

                <!-- Messages -->
                <template x-for="message in messages" :key="message.id">
                    <div class="flex" :class="message.isOwn ? 'justify-end' : 'justify-start'">
                        <div class="max-w-xs lg:max-w-xs">
                            <div x-show="!message.isOwn" class="text-xs text-gray-600 mb-1" x-text="message.user.name"></div>
                            <div class="rounded-xl lg:rounded-2xl px-3 lg:px-4 py-2 text-xs lg:text-sm" 
                                 :class="message.isOwn ? 'bg-emerald-500 text-white' : 'bg-white text-gray-800 border'">
                                <p x-text="message.message"></p>
                                <div class="text-xs mt-1 opacity-70" x-text="message.timestamp"></div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Typing Indicator -->
                <div x-show="isTyping" class="flex justify-start">
                    <div class="bg-white border rounded-xl lg:rounded-2xl px-3 lg:px-4 py-2">
                        <div class="flex space-x-1">
                            <div class="w-1.5 h-1.5 lg:w-2 lg:h-2 bg-gray-400 rounded-full animate-bounce"></div>
                            <div class="w-1.5 h-1.5 lg:w-2 lg:h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                            <div class="w-1.5 h-1.5 lg:w-2 lg:h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Input -->
        <div class="p-3 lg:p-4 border-t bg-white">
            <form @submit.prevent="sendMessage()" class="flex space-x-2">
                <input type="text" 
                       x-model="newMessage" 
                       placeholder="Type your message..." 
                       class="flex-1 border border-gray-300 rounded-full px-3 lg:px-4 py-2 text-xs lg:text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                       maxlength="500">
                <button type="submit" 
                        :disabled="!newMessage.trim() || sending"
                        class="bg-emerald-500 hover:bg-emerald-600 disabled:bg-gray-300 text-white rounded-full p-2 transition-colors duration-200">
                    <svg class="w-3 h-3 lg:w-4 lg:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function liveChat() {
    return {
        isOpen: false,
        messages: [],
        newMessage: '',
        sending: false,
        unreadCount: 0,
        onlineUsers: 1,
        isTyping: false,
        messageId: 1,
        currentUserId: null,
        currentUserName: null,

        init() {
            // Get current user data from meta tags
            this.currentUserId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
            this.currentUserName = document.querySelector('meta[name="user-name"]')?.getAttribute('content');

            // Restore unread count from localStorage
            this.unreadCount = parseInt(localStorage.getItem('chat-unread-count') || '0');

            // Load chat history when component initializes
            this.loadChatHistory();

            // Listen for new messages
            if (window.Echo) {
                window.Echo.channel('chat')
                    .listen('.message.sent', (e) => {
                        // Only show messages from OTHER users (not own messages)
                        if (e.user.id !== this.currentUserId) {
                            this.addMessage(e.message, e.user, e.timestamp, false);
                            
                            // If chat is closed, show notification
                            if (!this.isOpen) {
                                this.unreadCount++;
                                this.saveUnreadCount();
                            }
                        }
                    });
            }
        },

        async loadChatHistory() {
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
                        // Clear existing messages and load from database
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
                        
                        // Scroll to bottom after loading
                        this.$nextTick(() => {
                            this.scrollToBottom();
                        });
                    }
                }
            } catch (error) {
                console.error('Error loading chat history:', error);
            }
        },

        toggleChat() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.unreadCount = 0;
                this.saveUnreadCount();
                this.$nextTick(() => {
                    this.scrollToBottom();
                });
            }
        },

        saveUnreadCount() {
            localStorage.setItem('chat-unread-count', this.unreadCount.toString());
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
            container.scrollTop = container.scrollHeight;
        }
    }
}
</script>