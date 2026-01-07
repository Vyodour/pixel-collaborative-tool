<template>
    <div class="min-h-screen bg-gray-900 font-pixel text-white flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Retro Title -->
            <h1 class="text-4xl text-center mb-8 text-green-400 drop-shadow-[4px_4px_0_rgba(0,0,0,1)] hover:scale-105 transition-transform cursor-default" style="font-family: 'Press Start 2P', cursive;">
                PIXEL<br>BOARD
            </h1>

            <!-- Pixel Box -->
            <div class="bg-gray-800 border-4 border-white p-8 shadow-[8px_8px_0_0_rgba(0,0,0,0.5)] relative">
                <!-- Decoration Corners -->
                <div class="absolute -top-1 -left-1 w-2 h-2 bg-gray-900"></div>
                <div class="absolute -top-1 -right-1 w-2 h-2 bg-gray-900"></div>
                <div class="absolute -bottom-1 -left-1 w-2 h-2 bg-gray-900"></div>
                <div class="absolute -bottom-1 -right-1 w-2 h-2 bg-gray-900"></div>

                <h2 class="text-xl mb-6 text-center text-yellow-400 font-vt323 text-3xl">> NEW_PLAYER_ENTRY</h2>

                <form @submit.prevent="handleRegister" class="space-y-6">
                    <div class="group">
                        <label class="block text-xs mb-2 text-gray-400 font-bold tracking-wider group-hover:text-green-400 transition-colors">PLAYER NAME</label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            class="w-full bg-gray-900 border-2 border-gray-600 p-3 text-white focus:border-green-400 focus:outline-none transition-colors font-vt323 text-xl hover:border-gray-400"
                            placeholder="Player 1"
                            required
                        >
                    </div>

                    <div class="group">
                        <label class="block text-xs mb-2 text-gray-400 font-bold tracking-wider group-hover:text-green-400 transition-colors">EMAIL ADDRESS</label>
                        <input 
                            v-model="form.email" 
                            type="email" 
                            class="w-full bg-gray-900 border-2 border-gray-600 p-3 text-white focus:border-green-400 focus:outline-none transition-colors font-vt323 text-xl hover:border-gray-400"
                            placeholder="user@pixel.art"
                            required
                        >
                    </div>

                    <div class="group">
                        <label class="block text-xs mb-2 text-gray-400 font-bold tracking-wider group-hover:text-green-400 transition-colors">PASSWORD</label>
                        <input 
                            v-model="form.password" 
                            type="password" 
                            class="w-full bg-gray-900 border-2 border-gray-600 p-3 text-white focus:border-green-400 focus:outline-none transition-colors font-vt323 text-xl hover:border-gray-400"
                            placeholder="********"
                            required
                        >
                    </div>

                    <div class="group">
                        <label class="block text-xs mb-2 text-gray-400 font-bold tracking-wider group-hover:text-green-400 transition-colors">CONFIRM PASSWORD</label>
                        <input 
                            v-model="form.password_confirmation" 
                            type="password" 
                            class="w-full bg-gray-900 border-2 border-gray-600 p-3 text-white focus:border-green-400 focus:outline-none transition-colors font-vt323 text-xl hover:border-gray-400"
                            placeholder="********"
                            required
                        >
                    </div>

                    <div v-if="error" class="text-red-500 text-xs text-center font-bold animate-pulse">
                        [ERROR]: {{ error }}
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-green-500 text-white font-bold py-3 px-4 border-b-4 border-green-700 hover:bg-green-400 hover:border-green-600 active:border-b-0 active:translate-y-1 active:mt-1 transition-all cursor-pointer transform hover:scale-[1.02]"
                        style="font-family: 'Press Start 2P', cursive; font-size: 10px;"
                    >
                        REGISTER
                    </button>
                </form>

                <div class="mt-6 text-center text-xs text-gray-500">
                    <p>ALREADY HAVE ACCOUNT?</p>
                    <a href="/login" class="text-blue-400 hover:text-blue-300 underline decoration-2 decoration-blue-400 hover:decoration-blue-300 transition-colors cursor-pointer inline-block mt-2 hover:scale-110">> RESUME GAME</a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import axios from 'axios';

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
});
const error = ref('');

const handleRegister = async () => {
    try {
        await axios.get('/sanctum/csrf-cookie');
        // Changed to /register (handled by web.php)
        const response = await axios.post('/register', form);
        
         if (response.status === 201 || response.status === 200) {
            window.location.href = '/dashboard';
        }
        
    } catch (e) {
        error.value = e.response?.data?.message || 'REGISTRATION FAILED';
    }
};
</script>
