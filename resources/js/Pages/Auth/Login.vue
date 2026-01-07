<template>
    <div class="min-h-screen bg-gray-900 font-pixel text-white flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Retro Title -->
            <h1 class="text-4xl text-center mb-8 text-yellow-400 drop-shadow-[4px_4px_0_rgba(0,0,0,1)] hover:scale-105 transition-transform cursor-default" style="font-family: 'Press Start 2P', cursive;">
                PIXEL<br>BOARD
            </h1>

            <!-- Pixel Box -->
            <div class="bg-gray-800 border-4 border-white p-8 shadow-[8px_8px_0_0_rgba(0,0,0,0.5)] relative">
                <!-- Decoration Corners -->
                <div class="absolute -top-1 -left-1 w-2 h-2 bg-gray-900"></div>
                <div class="absolute -top-1 -right-1 w-2 h-2 bg-gray-900"></div>
                <div class="absolute -bottom-1 -left-1 w-2 h-2 bg-gray-900"></div>
                <div class="absolute -bottom-1 -right-1 w-2 h-2 bg-gray-900"></div>

                <h2 class="text-xl mb-6 text-center text-green-400 font-vt323 text-3xl">> LOGIN_SYSTEM</h2>

                <form @submit.prevent="handleLogin" class="space-y-6">
                    <div class="group">
                        <label class="block text-xs mb-2 text-gray-400 font-bold tracking-wider group-hover:text-yellow-400 transition-colors">EMAIL ADDRESS</label>
                        <input 
                            v-model="form.email" 
                            type="email" 
                            class="w-full bg-gray-900 border-2 border-gray-600 p-3 text-white focus:border-yellow-400 focus:outline-none transition-all font-vt323 text-xl hover:border-gray-400"
                            placeholder="user@pixel.art"
                            required
                        >
                    </div>

                    <div class="group">
                        <label class="block text-xs mb-2 text-gray-400 font-bold tracking-wider group-hover:text-yellow-400 transition-colors">PASSWORD</label>
                        <input 
                            v-model="form.password" 
                            type="password" 
                            class="w-full bg-gray-900 border-2 border-gray-600 p-3 text-white focus:border-yellow-400 focus:outline-none transition-all font-vt323 text-xl hover:border-gray-400"
                            placeholder="********"
                            required
                        >
                    </div>

                    <div v-if="error" class="text-red-500 text-xs text-center font-bold animate-pulse">
                        [ERROR]: {{ error }}
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-yellow-400 text-black font-bold py-3 px-4 border-b-4 border-yellow-600 hover:bg-yellow-300 hover:border-yellow-500 active:border-b-0 active:translate-y-1 active:mt-1 transition-all cursor-pointer transform hover:scale-[1.02]"
                        style="font-family: 'Press Start 2P', cursive; font-size: 10px;"
                    >
                        START GAME
                    </button>
                </form>

                <div class="mt-6 text-center text-xs text-gray-500">
                    <p>NEW PLAYER?</p>
                    <a href="/register" class="text-blue-400 hover:text-blue-300 underline decoration-2 decoration-blue-400 hover:decoration-blue-300 transition-colors cursor-pointer inline-block mt-2 hover:scale-110">> CREATE ACCOUNT</a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import axios from 'axios';

const form = reactive({
    email: '',
    password: ''
});
const error = ref('');

const handleLogin = async () => {
    try {
        await axios.get('/sanctum/csrf-cookie');
        
        // Changed to /login (handled by web.php now for Session Support)
        const response = await axios.post('/login', form);
        
        if (response.status === 200) {
            window.location.href = '/dashboard';
        }
    } catch (e) {
        error.value = e.response?.data?.message || 'LOGIN FAILED';
    }
};
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&family=VT323&display=swap');

.font-vt323 {
    font-family: 'VT323', monospace;
}
</style>
