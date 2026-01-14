<template>
    <div class="flex flex-col h-[500px] bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden shadow-lg">
        <!-- Header -->
        <div class="p-4 border-b border-zinc-800 bg-zinc-900 flex justify-between items-center">
            <h3 class="text-white font-bold flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
                </svg>
                Team Chat
            </h3>
            <div class="flex items-center gap-2 text-xs text-zinc-500">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Live
            </div>
        </div>

        <!-- Messages Area -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4 custom-scrollbar bg-zinc-950" ref="messagesContainer">
            <div v-if="!canChat" class="h-full flex flex-col items-center justify-center text-zinc-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <p class="text-sm font-medium">Chat restricted</p>
                <p class="text-xs">Viewers cannot access team chat.</p>
            </div>

            <div v-else-if="messages.length === 0" class="text-center text-zinc-500 text-sm mt-10 italic">
                No messages yet. Start the conversation!
            </div>

            <div 
                v-else
                v-for="item in groupedMessages" 
                :key="item.id"
            >
                <!-- Date Header -->
                <div v-if="item.type === 'date'" class="flex justify-center my-4 sticky top-0 z-10">
                    <span class="bg-zinc-800 text-zinc-400 text-[10px] font-medium px-3 py-1 rounded-full shadow-sm border border-zinc-700/50">
                        {{ item.text }}
                    </span>
                </div>

                <!-- Message Item -->
                <div 
                    v-else
                    :class="['flex gap-3 max-w-[85%]', isSelf(item.data) ? 'ml-auto flex-row-reverse' : '']"
                >
                    <!-- Avatar -->
                    <div 
                        class="w-8 h-8 rounded-full bg-zinc-800 border border-zinc-700 flex items-center justify-center text-xs font-bold text-white shrink-0"
                        :title="item.data.user.name"
                    >
                        {{ item.data.user.name.charAt(0) }}
                    </div>

                    <!-- Message Bubble -->
                    <div class="group relative">
                        <div 
                            :class="['p-3 rounded-2xl text-sm min-w-[60px]', 
                                isSelf(item.data) 
                                    ? 'bg-emerald-600 text-white rounded-tr-sm' 
                                    : 'bg-zinc-800 text-zinc-200 rounded-tl-sm border border-zinc-700'
                            ]"
                        >
                            <!-- Sender Name (only for others) -->
                            <div v-if="!isSelf(item.data)" class="text-[10px] font-bold text-emerald-400 mb-1">
                                {{ item.data.user.name }}
                            </div>

                            <!-- Content -->
                            <div v-if="editingId === item.data.id" class="min-w-[200px]">
                                <textarea 
                                    v-model="editContent" 
                                    class="w-full bg-black/20 border border-white/20 rounded p-1 text-white text-sm outline-none focus:border-white/50"
                                    rows="2"
                                    @keydown.enter.exact.prevent="updateMessage(item.data)"
                                    @keydown.esc="cancelEdit"
                                ></textarea>
                                <div class="flex justify-end gap-2 mt-2">
                                    <button @click="cancelEdit" class="text-[10px] text-white/70 hover:text-white">Cancel</button>
                                    <button @click="updateMessage(item.data)" class="text-[10px] bg-white/20 hover:bg-white/30 px-2 py-0.5 rounded text-white font-medium">Save</button>
                                </div>
                            </div>
                            <div v-else>
                                <p class="whitespace-pre-wrap break-words">{{ item.data.content }}</p>
                                <div :class="['text-[10px] mt-1 flex items-center gap-1', isSelf(item.data) ? 'text-emerald-200' : 'text-zinc-500']">
                                    {{ formatTime(item.data.created_at) }}
                                    <span v-if="item.data.created_at !== item.data.updated_at" class="italic">(edited)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions Dropdown Trigger -->
                        <div 
                            v-if="(isSelf(item.data) || isOwner) && editingId !== item.data.id"
                            :class="['absolute top-0 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1', isSelf(item.data) ? 'right-full mr-2' : 'left-full ml-2']"
                        >
                            <button 
                                v-if="isSelf(item.data)"
                                @click="startEdit(item.data)" 
                                class="p-1.5 bg-zinc-800 rounded-full text-zinc-400 hover:text-white hover:bg-zinc-700 transition"
                                title="Edit"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                </svg>
                            </button>
                            <button 
                                @click="deleteMessage(item.data)"
                                class="p-1.5 bg-zinc-800 rounded-full text-zinc-400 hover:text-red-400 hover:bg-zinc-700 transition"
                                title="Delete"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
             <div ref="scrollAnchor"></div>
        </div>

        <!-- Input Area -->
        <div class="p-4 bg-zinc-900 border-t border-zinc-800">
            <form v-if="canChat" @submit.prevent="sendMessage" class="flex gap-2 relative">
                <input 
                    v-model="newMessage" 
                    type="text" 
                    placeholder="Type a message..." 
                    class="flex-1 bg-zinc-950 border border-zinc-700 rounded-lg px-4 py-2.5 text-white text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition outline-none"
                    :disabled="isSending"
                >
                <button 
                    type="submit" 
                    class="bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg px-4 flex items-center justify-center transition disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="!newMessage.trim() || isSending"
                >
                    <svg v-if="!isSending" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                    </svg>
                    <svg v-else class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </form>
            <div v-else class="text-center text-xs text-zinc-500 italic py-2">
                Join as member to participate in chat.
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    projectId: { type: Number, required: true },
    inviteCode: { type: String, required: true },
    currentUser: { type: Object, required: true },
    projectOwnerId: { type: Number, required: true },
    userRole: { type: String, default: 'viewer' }
});

const messages = ref([]);
const newMessage = ref('');
const isSending = ref(false);
const messagesContainer = ref(null);
const scrollAnchor = ref(null);

const canChat = computed(() => true); 

const editingId = ref(null);
const editContent = ref('');

const isOwner = computed(() => props.currentUser.id === props.projectOwnerId);

const isSelf = (msg) => msg.user_id === props.currentUser.id;

const formatTime = (isoString) => {
    return new Date(isoString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const groupedMessages = computed(() => {
    const groups = [];
    let lastDate = null;

    messages.value.forEach(msg => {
        const msgDate = new Date(msg.created_at);
        const dateStr = msgDate.toDateString();

        if (lastDate !== dateStr) {
            groups.push({ type: 'date', text: formatDateHeader(msgDate), id: 'date-' + dateStr });
            lastDate = dateStr;
        }
        groups.push({ type: 'message', data: msg, id: msg.id });
    });

    return groups;
});

const formatDateHeader = (date) => {
    const today = new Date();
    const isToday = date.toDateString() === today.toDateString();
    
    if (isToday) return 'Today';

    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);
    if (date.toDateString() === yesterday.toDateString()) return 'Yesterday';

    const diffTime = Math.abs(today - date);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays <= 7) {
        return date.toLocaleDateString('en-US', { weekly: 'long' }).split(',')[0]; // Day name: "Monday"
    }

    if (date.getFullYear() === today.getFullYear()) {
         return date.toLocaleDateString('en-US', { day: 'numeric', month: 'long' }); // "12 January"
    }

    return date.toLocaleDateString('en-US', { day: 'numeric', month: 'long', year: 'numeric' }); // "12 January 2024"
};

const scrollToBottom = async () => {
    await nextTick();
    scrollAnchor.value?.scrollIntoView({ behavior: 'smooth' });
};

// ... existing code ...

const fetchMessages = async () => {
    if (!canChat.value) return; 
    try {
        const response = await axios.get(`/projects/${props.inviteCode}/messages`);
        messages.value = response.data;
        scrollToBottom();
    } catch (e) {
        console.error("Failed to fetch messages", e);
    }
};

const sendMessage = async () => {
    if (!canChat.value) return;
    if (!newMessage.value.trim() || isSending.value) return;
    
    const content = newMessage.value;
    newMessage.value = ''; // Optimistic clear
    isSending.value = true;

    try {
        const response = await axios.post(`/projects/${props.inviteCode}/messages`, { content });
        
        const msg = response.data;
        if (!messages.value.find(m => m.id === msg.id)) {
                messages.value.push(msg);
                scrollToBottom();
        }
    } catch (e) {
        console.error("Send failed", e);
        newMessage.value = content; // Restore
    } finally {
        isSending.value = false;
    }
};

const deleteMessage = async (msg) => {
    if (!confirm('Are you sure you want to delete this message?')) return;

    try {
        await axios.delete(`/projects/${props.projectId}/messages/${msg.id}`);
        messages.value = messages.value.filter(m => m.id !== msg.id);
    } catch (e) {
        console.error("Delete failed", e);
    }
};

// ... startEdit, cancelEdit same ...

const updateMessage = async (msg) => {
    if (!editContent.value.trim()) return;

    const newContent = editContent.value;
    editingId.value = null; 

    try {
         await axios.patch(`/projects/${props.projectId}/messages/${msg.id}`, { content: newContent });
        
        const existing = messages.value.find(m => m.id === msg.id);
        if (existing) {
            existing.content = newContent;
            existing.updated_at = new Date().toISOString();
        }

    } catch (e) {
        console.error("Update failed", e);
    }
};

onMounted(() => {
    if (canChat.value) {
        fetchMessages();

        if (window.Echo) {
            window.Echo.join(`project.${props.projectId}`)
                .listen('.message.sent', (e) => {
                    if (!messages.value.find(m => m.id === e.message.id)) {
                        messages.value.push(e.message);
                        scrollToBottom();
                    }
                })
                .listen('.message.updated', (e) => {
                    const idx = messages.value.findIndex(m => m.id === e.message.id);
                    if (idx !== -1) {
                        messages.value[idx] = e.message;
                    }
                })
                .listen('.message.deleted', (e) => {
                     messages.value = messages.value.filter(m => m.id !== e.messageId);
                });
        }
    }
});

onUnmounted(() => {
    if (window.Echo) {
        window.Echo.leave(`project.${props.projectId}`);
    }
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #3f3f46;
    border-radius: 20px;
}
</style>
