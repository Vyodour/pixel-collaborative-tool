<template>
    <div class="min-h-screen bg-gray-900 text-white">
        <!-- Pixel Navbar -->
        <nav class="bg-gray-800 border-b-4 border-white p-4">
            <div class="container mx-auto flex justify-between items-center">
                <h1 class="text-xl text-yellow-400 drop-shadow-[2px_2px_0_rgba(0,0,0,1)]" style="font-family: 'Press Start 2P', cursive;">
                    PIXEL BOARD
                </h1>
                <div class="flex items-center gap-4">
                    <span class="font-vt323 text-xl text-green-400">PLAYER: {{ user?.name || 'Loading...' }}</span>
                    <button 
                        @click="handleLogout" 
                        class="bg-red-500 text-white px-3 py-1 text-xs border-b-4 border-red-700 hover:bg-red-400 active:border-b-0 active:translate-y-1"
                        style="font-family: 'Press Start 2P', cursive;"
                    >
                        LOGOUT
                    </button>
                </div>
            </div>
        </nav>

        <main class="container mx-auto p-8">
            <div class="bg-gray-800 border-4 border-white p-8 shadow-[8px_8px_0_0_rgba(0,0,0,0.5)]">
                <h2 class="text-2xl mb-6 text-blue-400 font-vt323">> DASHBOARD</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Create New Project Card -->
                    <div class="bg-gray-700 border-2 border-dashed border-gray-500 p-6 flex flex-col items-center justify-center cursor-pointer hover:bg-gray-600 hover:border-white transition-all group h-64">
                        <div class="text-4xl text-gray-400 group-hover:text-yellow-400 mb-4">+</div>
                        <span class="font-vt323 text-xl text-gray-300 group-hover:text-white">CREATE NEW CANVAS</span>
                    </div>

                    <!-- Example Project Card (Placeholder) -->
                    <div class="bg-gray-900 border-2 border-gray-600 p-4 relative group hover:border-blue-400 transition-all">
                        <div class="w-full h-40 bg-gray-800 mb-4 flex items-center justify-center text-gray-600 font-pixel text-xs">
                            [THUMBNAIL]
                        </div>
                        <h3 class="font-bold text-lg truncate">My First Pixel Art</h3>
                        <p class="text-xs text-gray-500 mt-2">Edited 2h ago</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const user = ref(null);

onMounted(async () => {
    try {
        const response = await axios.get('/api/user');
        user.value = response.data;
    } catch (e) {
        localStorage.removeItem('isLoggedIn');
        router.push('/login');
    }
});

const handleLogout = async () => {
    try {
        await axios.post('/api/logout');
        localStorage.removeItem('isLoggedIn');
        router.push('/login');
    } catch (e) {
        console.error('Logout failed', e);
    }
};
</script>

<style>
.font-vt323 {
    font-family: 'VT323', monospace;
}
</style>
