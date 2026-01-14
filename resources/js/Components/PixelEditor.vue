<template>
    <div class="h-[calc(100vh-140px)] flex flex-col font-pixel">
        <!-- Editor Header (Inside Vue for state awareness) -->
        <div class="mb-4 flex justify-between items-center bg-gray-800 p-3 border-4 border-white shadow-[4px_4px_0_0_rgba(0,0,0,0.5)]">
            <div class="flex items-center gap-4">
                <a href="/dashboard" class="text-gray-400 hover:text-white font-vt323 text-xl">< BACK</a>
                <h2 class="text-green-400 font-vt323 text-2xl">
                    FILE: {{ name.toUpperCase() }} [{{ width }}x{{ height }}]
                </h2>
            </div>
            
            <div class="flex gap-3">
                <div class="flex items-center gap-2 px-3 py-1 bg-zinc-900 border border-zinc-700 rounded-lg">
                    <div :class="['w-2 h-2 rounded-full animate-pulse', isConnected ? 'bg-emerald-500' : 'bg-red-500']"></div>
                    <span class="text-[10px] text-zinc-400 font-mono uppercase">{{ isConnected ? 'Online' : 'Offline' }}</span>
                </div>
                <!-- Export Button -->
                <button @click="exportCanvas" class="py-1 px-4 text-xs font-medium bg-zinc-700 hover:bg-zinc-600 text-white rounded-lg transition-colors flex items-center gap-2 shadow-sm border border-zinc-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export
                </button>

                <!-- Save Button with specialized style to indicate action -->
                <button v-if="canDraw" @click="saveProject" class="py-1 px-4 text-xs font-medium bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg transition-colors flex items-center gap-2 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l-3-3a1 1 0 00-1.414-1.414z" />
                        <path d="M7 13l3 3 3-3" /> 
                    <!-- Using a simple check or save icon -->
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Save Changes
                </button>
            </div>
        </div>

        <!-- Main Workspace -->
        <div class="flex-1 flex gap-4 overflow-hidden">
            <!-- Tools Palette (L) -->
            <div v-if="canDraw" class="w-16 bg-zinc-800 border-r border-zinc-700 p-2 flex flex-col gap-2 shadow-sm z-10">
                <button 
                    v-for="tool in tools" 
                    :key="tool.id"
                    @click="activeTool = tool.id"
                    :class="['w-full aspect-square flex items-center justify-center transition-all rounded-lg mb-1', activeTool === tool.id ? 'bg-emerald-600 text-white' : 'text-zinc-400 hover:bg-zinc-700 hover:text-white']"
                    :title="tool.name"
                >
                    <span v-html="tool.icon" class="w-6 h-6 flex items-center justify-center"></span>
                </button>
                
                <div class="mt-auto border-t border-zinc-700 pt-3 flex flex-col items-center gap-2">
                    <input type="color" v-model="primaryColor" class="w-8 h-8 rounded-full overflow-hidden cursor-pointer border-none p-0 bg-transparent ring-2 ring-zinc-600 hover:ring-white transition-all">
                </div>
            </div>

            <!-- Canvas Area (C) -->
            <div 
                ref="workspaceRef"
                :class="['flex-1 bg-zinc-900 shadow-inner flex items-center justify-center overflow-auto relative select-none', activeTool === 'pan' ? 'cursor-grab' : 'cursor-crosshair']"
                @mousedown="handleWorkspaceMouseDown"
                @mousemove="handleWorkspaceMouseMove"
                @mouseup="handleWorkspaceMouseUp"
                @wheel="handleWheel"
                @contextmenu.prevent
            >
                <div 
                    class="relative shadow-2xl transition-transform duration-75"
                    :style="{ transform: `scale(${zoom}) translate(${pan.x}px, ${pan.y}px)` }"
                >
                    <canvas 
                        ref="canvasRef" 
                        :width="width" 
                        :height="height"
                        class="image-pixelated bg-white shadow-lg"
                        @mousedown.stop="handleCanvasMouseDown"
                        @mousemove.stop="handleCanvasMouseMove"
                    ></canvas>
                    
    <!-- Comments Overlay -->
                    <div class="absolute inset-0 pointer-events-none overflow-visible">
                        <div 
                            v-for="comment in comments" 
                            :key="comment.id"
                            class="absolute pointer-events-auto group origin-top-left"
                            @mousedown.stop
                            :class="{ 'z-50': activeCommentId === comment.id, 'z-10': activeCommentId !== comment.id }"
                            :style="{ 
                                left: `${comment.x}px`, 
                                top: `${comment.y}px`,
                                transform: `scale(${1/zoom})`
                            }"
                        >
                            <div 
                                class="relative -ml-3 -mt-6 w-6 h-6 cursor-pointer"
                                @click.stop="activeCommentId = activeCommentId === comment.id ? null : comment.id"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-full h-full text-emerald-500 drop-shadow-md">
                                    <path fill-rule="evenodd" d="M4.804 21.644A6.707 6.707 0 006 21.75a6.721 6.721 0 003.583-1.029c.774.182 1.584.279 2.417.279 5.322 0 9.75-3.97 9.75-9 0-5.03-4.428-9-9.75-9s-9.75 3.97-9.75 9c0 2.409 1.025 4.587 2.674 6.192.232.226.277.428.254.543a3.73 3.73 0 01-.814 1.686.75.75 0 00.44 1.223zM8.25 10.875a1.125 1.125 0 100 2.25 1.125 1.125 0 000-2.25zM10.875 12a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0zm4.875-1.125a1.125 1.125 0 100 2.25 1.125 1.125 0 000-2.25z" clip-rule="evenodd" />
                                </svg>
                                <!-- Tooltip / Box -->
                                <div 
                                    v-if="activeCommentId === comment.id"
                                    class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-zinc-800 text-white p-3 rounded shadow-xl border border-zinc-600 cursor-default min-w-[200px]"
                                    @click.stop
                                >
                                    <div class="flex justify-between items-start border-b border-zinc-700 pb-2 mb-2">
                                        <div class="font-bold text-emerald-400 text-sm">{{ comment.user.name }}</div>
                                        <button 
                                            @click.stop="deleteComment(comment.id)"
                                            class="text-zinc-500 hover:text-red-500 transition-colors"
                                            title="Delete"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="text-zinc-200 text-sm leading-relaxed whitespace-pre-wrap">{{ comment.content }}</div>
                                    <div class="text-[10px] text-zinc-500 mt-2 text-right">{{ new Date(comment.created_at).toLocaleTimeString() }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- New Comment Input -->
                        <div 
                            v-if="isCommentInputVisible"
                            class="absolute pointer-events-auto z-[60] origin-top-left"
                            :style="{ 
                                left: `${commentInputPos.x}px`, 
                                top: `${commentInputPos.y}px`,
                                transform: `scale(${1/zoom})`
                            }"
                        >
                            <div class="bg-zinc-800 border border-zinc-600 p-2 rounded shadow-xl flex flex-col gap-2 min-w-[200px]">
                                <textarea 
                                    v-model="newCommentText" 
                                    class="w-full h-20 bg-zinc-900 border border-zinc-700 text-white text-xs p-2 rounded focus:outline-none focus:border-emerald-500 resize-none"
                                    placeholder="Type your comment..."
                                    @keydown.stop
                                    ref="commentInputRef"
                                ></textarea>
                                <div class="flex justify-end gap-2">
                                    <button @click="cancelComment" class="text-xs text-zinc-400 hover:text-white">Cancel</button>
                                    <button @click="submitComment" class="px-3 py-1 bg-emerald-600 text-white text-xs rounded hover:bg-emerald-500">Post</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Grid Overlay (Optional) -->
                    <div v-if="zoom >= 8" class="absolute inset-0 pointer-events-none grid" :style="gridStyle"></div>
                </div>

                <!-- Zoom Indicator -->
                <div class="absolute bottom-4 left-4 bg-black bg-opacity-60 px-2 py-1 text-[10px] text-gray-300">
                    ZOOM: {{ Math.round(zoom * 100) }}% | PAN: {{ pan.x }},{{ pan.y }}
                </div>
            </div>

            <!-- Layers/Info (R) -->
            <div class="w-64 bg-zinc-800 border-l border-zinc-700 p-4 flex flex-col gap-4 shadow-sm z-10">
                <div class="flex items-center justify-between border-b border-zinc-700 pb-2">
                    <h4 class="text-xs font-bold text-zinc-400 uppercase tracking-widest">Properties</h4>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <div class="text-[10px] text-zinc-500 mb-1 font-semibold uppercase">Current Tool</div>
                        <div class="flex items-center gap-2 text-sm text-emerald-400 font-medium">
                            {{ tools.find(t => t.id === activeTool)?.name }}
                        </div>
                    </div>

                    <div>
                        <div class="text-[10px] text-zinc-500 mb-1 font-semibold uppercase">Position</div>
                        <div class="text-xs font-mono text-zinc-300">X: {{ mousePos.x }} <span class="text-zinc-600">|</span> Y: {{ mousePos.y }}</div>
                    </div>

                    <!-- Shape Properties -->
                    <div v-if="activeTool === 'shape'" class="bg-zinc-900 p-3 rounded-lg border border-zinc-700 animate-fade-in">
                        <div class="text-[10px] text-zinc-500 mb-2 font-semibold uppercase">Shape Type</div>
                        <div class="flex gap-2 mb-4">
                            <button 
                                @click="shapeType = 'square'" 
                                :class="['flex-1 py-1 text-xs rounded border transition-all', shapeType === 'square' ? 'bg-emerald-600 border-emerald-500 text-white' : 'bg-zinc-800 border-zinc-600 text-zinc-400 hover:border-zinc-500']"
                            >Square</button>
                            <button 
                                @click="shapeType = 'circle'" 
                                :class="['flex-1 py-1 text-xs rounded border transition-all', shapeType === 'circle' ? 'bg-emerald-600 border-emerald-500 text-white' : 'bg-zinc-800 border-zinc-600 text-zinc-400 hover:border-zinc-500']"
                            >Circle</button>
                        </div>

                        <div class="text-[10px] text-zinc-500 mb-2 font-semibold uppercase">Style</div>
                        <div class="flex gap-2 mb-4">
                            <button 
                                @click="shapeStyle = 'fill'" 
                                :class="['flex-1 py-1 text-xs rounded border transition-all', shapeStyle === 'fill' ? 'bg-emerald-600 border-emerald-500 text-white' : 'bg-zinc-800 border-zinc-600 text-zinc-400 hover:border-zinc-500']"
                            >Fill</button>
                            <button 
                                @click="shapeStyle = 'stroke'" 
                                :class="['flex-1 py-1 text-xs rounded border transition-all', shapeStyle === 'stroke' ? 'bg-emerald-600 border-emerald-500 text-white' : 'bg-zinc-800 border-zinc-600 text-zinc-400 hover:border-zinc-500']"
                            >Outline</button>
                        </div>

                        <div class="text-[10px] text-zinc-500 mb-1 font-semibold uppercase flex justify-between">
                            <span>Rotation</span>
                            <span class="text-emerald-400">{{ shapeRotation }}°</span>
                        </div>
                        <input 
                            type="range" 
                            v-model.number="shapeRotation" 
                            min="0" 
                            max="360" 
                            class="w-full h-1 bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-emerald-500"
                        >
                    </div>

                    <div class="pt-4 border-t border-zinc-700">
                        <div class="text-[10px] text-zinc-500 mb-2 font-semibold uppercase">History</div>
                        <div class="flex gap-2">
                            <button @click="undo" :disabled="!canUndo" class="flex-1 py-1.5 bg-zinc-700 hover:bg-zinc-600 rounded-md disabled:opacity-30 disabled:hover:bg-zinc-700 text-zinc-300 text-xs font-medium transition-all" title="Undo (Ctrl+Z)">
                                Undo
                            </button>
                            <button @click="redo" :disabled="!canRedo" class="flex-1 py-1.5 bg-zinc-700 hover:bg-zinc-600 rounded-md disabled:opacity-30 disabled:hover:bg-zinc-700 text-zinc-300 text-xs font-medium transition-all" title="Redo (Ctrl+Y)">
                                Redo
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="mt-auto border-t border-zinc-700 pt-4 cursor-pointer hover:bg-zinc-700/50 transition-colors p-2 rounded-lg" @click="isUsersModalOpen = true">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="text-xs text-zinc-500 uppercase font-bold tracking-widest">Online Now</h4>
                        <span class="text-[10px] bg-emerald-900 text-emerald-400 px-1.5 py-0.5 rounded-full">{{ activeUsers.length }}</span>
                    </div>
                    <div class="flex -space-x-2 overflow-hidden py-1">
                        <div 
                            v-for="user in activeUsers" 
                            :key="user.id"
                            class="w-8 h-8 bg-zinc-700 border-2 border-zinc-800 rounded-full flex items-center justify-center text-xs text-white font-bold ring-2 ring-zinc-800 transition-transform hover:scale-110 hover:z-10 cursor-help" 
                            :title="user.name"
                        >
                            {{ user.name.charAt(0) }}
                        </div>
                        <div v-if="activeUsers.length === 0" class="text-xs text-zinc-500 italic">No one else here...</div>
                    </div>
                </div>
            </div>
        </div>

        <TransitionGroup 
            tag="div"
            class="fixed top-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 z-[100]"
            enter-from-class="opacity-0 -translate-y-4"
            enter-active-class="transition-all duration-300"
            leave-to-class="opacity-0 translate-y-2"
            leave-active-class="transition-all duration-300"
        >
            <div v-for="notif in notifications" :key="notif.id" :class="['px-6 py-2 border-4 border-white shadow-[4px_4px_0_0_rgba(0,0,0,0.5)] font-vt323 text-lg', notif.type === 'success' ? 'bg-green-600' : 'bg-blue-600']">
                {{ notif.text }}
            </div>
        </TransitionGroup>

        <!-- Online Users Modal -->
        <div v-if="isUsersModalOpen" class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="isUsersModalOpen = false">
            <div class="bg-zinc-900 border-4 border-white shadow-[8px_8px_0_0_rgba(0,0,0,0.5)] w-full max-w-sm p-6 transform transition-all">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-vt323 text-emerald-400 px-2 border-l-4 border-emerald-500">ONLINE USERS ({{ activeUsers.length }})</h3>
                    <button @click="isUsersModalOpen = false" class="text-zinc-500 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="space-y-2 max-h-[60vh] overflow-y-auto custom-scrollbar">
                    <div v-for="user in activeUsers" :key="user.id" class="flex items-center gap-3 p-3 bg-zinc-800 border border-zinc-700 hover:border-emerald-500/50 transition-colors">
                        <div class="w-8 h-8 bg-emerald-700 rounded-full flex items-center justify-center font-bold text-white shadow-inner">
                            {{ user.name.charAt(0) }}
                        </div>
                        <div class="flex-1">
                            <div class="font-vt323 text-lg text-white leading-none">{{ user.name }}</div>
                            <div class="text-[10px] text-zinc-500 uppercase font-mono mt-1">Connected</div>
                        </div>
                        <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.5)]"></div>
                    </div>
                </div>
                
                <div class="mt-6 pt-4 border-t border-zinc-800 text-center">
                    <button @click="isUsersModalOpen = false" class="w-full py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 font-vt323 text-lg border-2 border-zinc-700 transition-colors">
                        CLOSE
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, onUnmounted } from 'vue';

// ... props ...
const props = defineProps({
    projectId: Number,
    canvasId: Number,
    width: Number,
    height: Number,
    name: String,
    initialData: String,
    bgColor: String,
    currentUserId: Number,
    initialComments: Array,
    userRole: {
        type: String, // 'owner', 'editor', 'member', 'viewer'
        default: 'viewer' 
    }
});

// Permissions
// Permissions
const isViewer = computed(() => props.userRole === 'viewer');
const canDraw = computed(() => ['owner', 'editor', 'member'].includes(props.userRole));
const canManage = computed(() => ['owner', 'editor'].includes(props.userRole));

onMounted(() => {
    console.log('PixelEditor Mounted. Role:', props.userRole);
    if (!props.canvasId) {
        console.error('Canvas ID is missing!');
    }
    // ... existing mounted logic if any
});


// Canvas & Context
const canvasRef = ref(null);
const workspaceRef = ref(null);
let ctx = null;

const deleteComment = async (commentId) => {
    try {
        const response = await fetch(`/canvases/${props.canvasId}/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        if (!response.ok) {
            throw new Error(`Server returned ${response.status} ${response.statusText}`);
        }

        // Optimistic UI update
        comments.value = comments.value.filter(c => c.id !== commentId);
        if (activeCommentId.value === commentId) activeCommentId.value = null;
    } catch (e) {
        // Keep error logging for critical failures, but remove verbose debug logs
        console.error("Failed to delete comment", e);
        addNotification("Failed to delete comment", 'error');
    }
};

const isConnected = ref(false);
const zoom = ref(10);
const pan = reactive({ x: 0, y: 0 });
const primaryColor = ref('#000000');
const activeTool = ref('pencil');
const isDrawing = ref(false);
const isPanning = ref(false);
const mousePos = reactive({ x: 0, y: 0 });
const notifications = ref([]);
const activeUsers = ref([]);
const comments = ref(props.initialComments || []);
const activeCommentId = ref(null); // Track open comment
const isCommentInputVisible = ref(false);
const commentInputPos = reactive({ x: 0, y: 0 });
const newCommentText = ref('');
const commentInputRef = ref(null);

const isUsersModalOpen = ref(false);

const tools = [
    { 
        id: 'pencil', 
        name: 'Pencil (B)', 
        icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>' 
    },
    { 
        id: 'eraser', 
        name: 'Eraser (E)', 
        icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>' 
    },
    { 
        id: 'bucket', 
        name: 'Bucket (G)', 
        icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M19.22 8l-2.09-2.09-2.09 2.09 4.18 4.18c.27-.27.27-.72 0-.99L19.22 8zM3.46 13.3c-.27.27-.27.72 0 .99l3.18 3.18c.27.27.72.27.99 0l8.77-8.77L12.22 4.52 3.46 13.3zM15 17c0 1.66 1.34 3 3 3s3-1.34 3-3-1.34-3-3-3-3 1.34-3 3z"/></svg>' 
    },
    {
        id: 'pan',
        name: 'Pan (H)',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" /></svg>'
    },
    {
        id: 'comment',
        name: 'Comment (C)',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg>'
    },
    {
        id: 'shape',
        name: 'Shape (S)',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2" /></svg>'
    }
];

const gridStyle = computed(() => ({
    gridTemplateColumns: `repeat(${props.width}, 1fr)`,
    gridTemplateRows: `repeat(${props.height}, 1fr)`,
    backgroundImage: `linear-gradient(to right, rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,0.1) 1px, transparent 1px)`,
    backgroundSize: `${100 / props.width}% ${100 / props.height}%`
}));

const shapeType = ref('square');
const shapeStyle = ref('fill'); 
const shapeRotation = ref(0);
const startDragPos = reactive({ x: 0, y: 0 });
let canvasSnapshot = null;

const history = ref([]);
const redoStack = ref([]);
const maxHistory = 50;

const canUndo = computed(() => history.value.length > 0);
const canRedo = computed(() => redoStack.value.length > 0);

const addNotification = (text, type = 'success') => {
    const id = Date.now();
    notifications.value.push({ id, text, type });
    setTimeout(() => {
        notifications.value = notifications.value.filter(n => n.id !== id);
    }, 3000);
};

onMounted(() => {
    ctx = canvasRef.value.getContext('2d', { willReadFrequently: true });
    ctx.imageSmoothingEnabled = false;
    
    if (props.initialData) {
        const img = new Image();
        img.onload = () => {
            ctx.clearRect(0, 0, props.width, props.height);
            ctx.drawImage(img, 0, 0);
        };
        img.src = props.initialData;
    } else {
        ctx.fillStyle = props.bgColor || '#ffffff';
        ctx.fillRect(0, 0, props.width, props.height);
    }

    window.addEventListener('keydown', handleKeyDown);

    if (window.Echo) {
        const channelName = `canvas.${props.canvasId}`;
        console.log(`🔗 Joining channel: ${channelName}`);
        
        window.Echo.join(channelName)
            .here((users) => {
                activeUsers.value = users;
            })
            .joining((user) => {
                activeUsers.value.push(user);
                addNotification(`${user.name} joined.`, 'info');
            })
            .leaving((user) => {
                const index = activeUsers.value.findIndex(u => u.id === user.id);
                if (index > -1) activeUsers.value.splice(index, 1);
                addNotification(`${user.name} left.`, 'info');
            })
            .listen('.pixel.painted', (e) => {
                drawPixel(e.x, e.y, e.color, false); 
            })
            .listen('.canvas.saved', (e) => {
                addNotification(`${e.userName} saved changes. Updating canvas...`, 'info');
                if (e.data && e.canvasId === props.canvasId) {
                    const img = new Image();
                    img.onload = () => {
                        ctx.clearRect(0, 0, props.width, props.height);
                        ctx.drawImage(img, 0, 0);
                        saveToHistory();
                    };
                    img.src = e.data;
                }
            })
            .listen('.comment.added', (e) => {
                console.log('💬 Comment added:', e.comment);
                comments.value.push(e.comment);
                addNotification(`New comment from ${e.comment.user.name}`, 'info');
            })
            .listen('.comment.deleted', (e) => {
                 comments.value = comments.value.filter(c => c.id !== e.commentId);
             })
            .error((error) => {
                console.error('❌ Echo Error:', error);
                isConnected.value = false;
            });
            
            window.Echo.connector.pusher.connection.bind('connected', () => {
                isConnected.value = true;
            });
            window.Echo.connector.pusher.connection.bind('unavailable', () => {
                isConnected.value = false;
            });
    } else {
        console.error('❌ window.Echo is not available!');
    }
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
    if (window.Echo) {
        window.Echo.leave(`canvas.${props.canvasId}`);
    }
});



const saveToHistory = () => {
    const snapshot = ctx.getImageData(0, 0, props.width, props.height);
    history.value.push(snapshot);
    if (history.value.length > maxHistory) history.value.shift();
    redoStack.value = []; // Clear redo stack on new action
};

const undo = () => {
    if (!canUndo.value) return;
    const current = ctx.getImageData(0, 0, props.width, props.height);
    redoStack.value.push(current);
    
    const previous = history.value.pop();
    ctx.putImageData(previous, 0, 0);
};

const redo = () => {
    if (!canRedo.value) return;
    const current = ctx.getImageData(0, 0, props.width, props.height);
    history.value.push(current);
    
    const next = redoStack.value.pop();
    ctx.putImageData(next, 0, 0);
};

const handleKeyDown = (e) => {
    const isCtrl = e.ctrlKey || e.metaKey;

    if (e.key.toLowerCase() === 'b') activeTool.value = 'pencil';
    if (e.key.toLowerCase() === 'e') activeTool.value = 'eraser';
    if (e.key.toLowerCase() === 'g') activeTool.value = 'bucket';
    if (e.key.toLowerCase() === 'h') activeTool.value = 'pan';
    if (e.key.toLowerCase() === 'c') activeTool.value = 'comment';
    
    if (isCtrl && e.key.toLowerCase() === 'z') {
        e.preventDefault();
        undo();
    }
    if (isCtrl && e.key.toLowerCase() === 'y') {
        e.preventDefault();
        redo();
    }
    if (isCtrl && e.key.toLowerCase() === 's') {
        e.preventDefault();
        saveProject();
    }

    if (e.key === '0') { zoom.value = 10; pan.x = 0; pan.y = 0; }
};

const getPixelCoords = (e) => {
    const rect = canvasRef.value.getBoundingClientRect();
    const x = Math.floor((e.clientX - rect.left) / (zoom.value));
    const y = Math.floor((e.clientY - rect.top) / (zoom.value));
    return { x, y };
};

const drawPixel = (x, y, color, shouldBroadcast = true) => {
    if (x < 0 || x >= props.width || y < 0 || y >= props.height) return;
    
    const p = ctx.getImageData(x, y, 1, 1).data;
    const hex = "#" + ((1 << 24) + (p[0] << 16) + (p[1] << 8) + p[2]).toString(16).slice(1);
    if (hex.toLowerCase() === color.toLowerCase()) return;

    ctx.fillStyle = color;
    ctx.fillRect(x, y, 1, 1);

    if (shouldBroadcast) {
        broadcastPaint(x, y, color);
    }
};

const broadcastPaint = (x, y, color) => {
    fetch(`/canvases/${props.canvasId}/paint`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ x, y, color })
    }).catch(err => console.error("Broadcast failed", err));
};

const floodFill = (startX, startY, fillColor) => {
    const imageData = ctx.getImageData(0, 0, props.width, props.height);
    const data = imageData.data;
    
    const startIdx = (startY * props.width + startX) * 4;
    const startR = data[startIdx];
    const startG = data[startIdx+1];
    const startB = data[startIdx+2];
    const startA = data[startIdx+3];

    const tempCanvas = document.createElement('canvas');
    tempCanvas.width = 1; tempCanvas.height = 1;
    const tctx = tempCanvas.getContext('2d');
    tctx.fillStyle = fillColor;
    tctx.fillRect(0,0,1,1);
    const fillRGBA = tctx.getImageData(0,0,1,1).data;

    if (startR === fillRGBA[0] && startG === fillRGBA[1] && startB === fillRGBA[2] && startA === fillRGBA[3]) return;

    const stack = [[startX, startY]];
    
    while (stack.length > 0) {
        const [x, y] = stack.pop();
        const idx = (y * props.width + x) * 4;

        if (data[idx] === startR && data[idx+1] === startG && data[idx+2] === startB && data[idx+3] === startA) {
            data[idx] = fillRGBA[0];
            data[idx+1] = fillRGBA[1];
            data[idx+2] = fillRGBA[2];
            data[idx+3] = fillRGBA[3];

            if (x > 0) stack.push([x-1, y]);
            if (x < props.width - 1) stack.push([x+1, y]);
            if (y > 0) stack.push([x, y-1]);
            if (y < props.height - 1) stack.push([x, y+1]);
        }
    }
    ctx.putImageData(imageData, 0, 0);
};

const handleCanvasMouseDown = (e) => {
    if (e.button === 1) {
        isPanning.value = true;
        return;
    }
    
    if (activeTool.value === 'comment') {
         const { x, y } = getPixelCoords(e);
         commentInputPos.x = x;
         commentInputPos.y = y;
         newCommentText.value = '';
         isCommentInputVisible.value = true;
         setTimeout(() => commentInputRef.value?.focus(), 100);
         return;
    }

    saveToHistory();

    if (activeTool.value === 'pan') {
        isPanning.value = true;
        return;
    }

    const { x, y } = getPixelCoords(e);

    if (activeTool.value === 'shape') {
        isDrawing.value = true;
        startDragPos.x = x;
        startDragPos.y = y;
        canvasSnapshot = ctx.getImageData(0, 0, props.width, props.height);
        return;
    }

    if (activeTool.value === 'bucket') {
        floodFill(x, y, primaryColor.value);
        return;
    }

    isDrawing.value = true;
    const color = activeTool.value === 'eraser' ? (props.bgColor || '#ffffff') : primaryColor.value;
    drawPixel(x, y, color);
};

const cancelComment = () => {
    isCommentInputVisible.value = false;
    newCommentText.value = '';
};

const submitComment = async () => {
    if (!newCommentText.value.trim()) return;

    try {
        const response = await fetch(`/canvases/${props.canvasId}/comments`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ 
                x: commentInputPos.x, 
                y: commentInputPos.y, 
                content: newCommentText.value 
            })
        });

        if (response.ok) {
            const comment = await response.json();
            comments.value.push(comment); 
            isCommentInputVisible.value = false;
            newCommentText.value = '';
        }
    } catch (e) {
        console.error("Failed to post comment", e);
        addNotification("Failed to post comment", 'error');
    }
};

const drawPixelLine = (x0, y0, x1, y1, color) => {
    let dx = Math.abs(x1 - x0);
    let dy = Math.abs(y1 - y0);
    let sx = (x0 < x1) ? 1 : -1;
    let sy = (y0 < y1) ? 1 : -1;
    let err = dx - dy;

    while (true) {
        drawPixel(x0, y0, color, false);
        if ((x0 === x1) && (y0 === y1)) break;
        let e2 = 2 * err;
        if (e2 > -dy) { err -= dy; x0 += sx; }
        if (e2 < dx) { err += dx; y0 += sy; }
    }
};

const drawPixelCircle = (xc, yc, r, color, fill = false) => {
    let x = 0;
    let y = r;
    let d = 3 - 2 * r;
    
    const plotOctants = (cx, cy, x, y) => {
        const points = [
            { x: cx + x, y: cy + y }, { x: cx - x, y: cy + y },
            { x: cx + x, y: cy - y }, { x: cx - x, y: cy - y },
            { x: cx + y, y: cy + x }, { x: cx - y, y: cy + x },
            { x: cx + y, y: cy - x }, { x: cx - y, y: cy - x }
        ];

        points.forEach(p => drawPixel(p.x, p.y, color, false));

        if (fill) {
            const scanline = (x1, x2, y) => {
                 for (let i = Math.min(x1, x2); i <= Math.max(x1, x2); i++) {
                     drawPixel(i, y, color, false);
                 }
            };
            scanline(cx - x, cx + x, cy + y);
            scanline(cx - x, cx + x, cy - y);
            scanline(cx - y, cx + y, cy + x);
            scanline(cx - y, cx + y, cy - x);
        }
    };

    while (y >= x) {
        plotOctants(xc, yc, x, y);
        x++;
        if (d > 0) {
            y--;
            d = d + 4 * (x - y) + 10;
        } else {
            d = d + 4 * x + 6;
        }
    }
};

const handleCanvasMouseMove = (e) => {
    const { x, y } = getPixelCoords(e);
    mousePos.x = x;
    mousePos.y = y;

    if (activeTool.value === 'shape' && isDrawing.value) {
        ctx.putImageData(canvasSnapshot, 0, 0);
        
        const w = x - startDragPos.x;
        const h = y - startDragPos.y;
        const cx = Math.round(startDragPos.x + w / 2);
        const cy = Math.round(startDragPos.y + h / 2);
        const r = Math.round(Math.abs(w + h) / 4);

        if (shapeStyle.value === 'stroke' && shapeType.value === 'square') {
             const x0 = startDragPos.x, y0 = startDragPos.y;
             const x1 = x, y1 = y;
             drawPixelLine(x0, y0, x1, y0, primaryColor.value);
             drawPixelLine(x0, y1, x1, y1, primaryColor.value);
             drawPixelLine(x0, y0, x0, y1, primaryColor.value);
             drawPixelLine(x1, y0, x1, y1, primaryColor.value);
        }
        else if (shapeType.value === 'square') {
             const xStart = Math.min(startDragPos.x, x);
             const yStart = Math.min(startDragPos.y, y);
             const width = Math.abs(w);
             const height = Math.abs(h);
             for(let i=xStart; i<=xStart+width; i++) {
                 for(let j=yStart; j<=yStart+height; j++) {
                     drawPixel(i, j, primaryColor.value, false);
                 }
             }
        }
        else if (shapeType.value === 'circle') {
             const radius = Math.floor(Math.max(Math.abs(w), Math.abs(h)) / 2);
              drawPixelCircle(cx, cy, radius, primaryColor.value, shapeStyle.value === 'fill');
        }
        
        return;
    }

    if (isDrawing.value) {
        const color = activeTool.value === 'eraser' ? (props.bgColor || '#ffffff') : primaryColor.value;
        drawPixel(x, y, color);
    }
};

const handleWorkspaceMouseDown = (e) => {
    if (e.button === 1 || e.altKey) {
        isPanning.value = true;
    } else {
        // Prevent drawing/tools if viewer
        if (!canDraw.value) return;

        activeCommentId.value = null;

        if (isCommentInputVisible.value && !e.target.closest('.bg-zinc-800')) {
            cancelComment();
        }
    }
};

const handleWorkspaceMouseMove = (e) => {
    if (isPanning.value) {
        pan.x += e.movementX / zoom.value;
        pan.y += e.movementY / zoom.value;
    }
};

const handleWorkspaceMouseUp = () => {
    if (activeTool.value === 'shape' && isDrawing.value) {
        addNotification('Shape created. Don\'t forget to Save!', 'info');
    }
    isDrawing.value = false;
    isPanning.value = false;
};

const exportCanvas = () => {
    const scale = 20; // 20x scale for visibility
    const tempCanvas = document.createElement('canvas');
    tempCanvas.width = props.width * scale;
    tempCanvas.height = props.height * scale;
    const tctx = tempCanvas.getContext('2d');
    
    // Disable smoothing for pixel art look
    tctx.imageSmoothingEnabled = false;
    
    // Draw current canvas scaled up
    tctx.drawImage(canvasRef.value, 0, 0, tempCanvas.width, tempCanvas.height);
    
    const link = document.createElement('a');
    link.download = `${props.name.replace(/\s+/g, '_') || 'pixel-art'}.png`;
    link.href = tempCanvas.toDataURL('image/png');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    addNotification('Image downloaded successfully!', 'success');
};

const handleWheel = (e) => {
    if (e.ctrlKey || e.metaKey) {
        e.preventDefault();
        const delta = e.deltaY > 0 ? -1 : 1;
        zoom.value = Math.max(1, Math.min(50, zoom.value + delta));
    }
};

const saveProject = async (silent = false) => {
    try {
        const response = await fetch(`/canvases/${props.canvasId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Socket-ID': window.Echo && window.Echo.socketId() ? window.Echo.socketId() : null
            },
            body: JSON.stringify({
                data: canvasRef.value.toDataURL() // Save as PNG base64 for now
            })
        });

        if (response.ok) {
            if (!silent) addNotification('WORKSPACE_SAFE: Data synchronized.');
        }
    } catch (error) {
        console.error('Save failed:', error);
        addNotification('SAVE_FAILED: Check connection.', 'error');
    }
};

</script>

<style scoped>
.font-pixel {
    font-family: 'VT323', monospace;
}
.image-pixelated {
    image-rendering: -moz-crisp-edges;
    image-rendering: -webkit-optimize-contrast;
    image-rendering: pixelated;
    image-rendering: optimize-speed;
}

canvas {
    image-rendering: pixelated;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fadeIn 0.3s ease-out forwards;
}
</style>
