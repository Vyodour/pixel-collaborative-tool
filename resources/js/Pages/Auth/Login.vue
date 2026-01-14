<template>
    <div class="min-h-screen bg-zinc-950 font-pixel text-white flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Brand -->
            <div class="flex flex-col items-center mb-8">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center mb-4 ring-1 ring-emerald-500/20 shadow-lg shadow-emerald-500/10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M4 2a2 2 0 00-2 2v11a3 3 0 106 0V4a2 2 0 00-2-2H4zm1 14a1 1 0 100-2 1 1 0 000 2zm5-1.757l4.9-4.9a2 2 0 000-2.828L13.485 5.1a2 2 0 00-2.828 0L10 5.757v8.486zM16 18H9.071l6-6H16a2 2 0 012 2v2a2 2 0 01-2 2z" clip-rule="evenodd" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-white">PixelBoard</h1>
                <p class="text-zinc-400 text-sm mt-2">Sign in to your workspace</p>
            </div>

            <!-- Login Card -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-8 shadow-xl">
                <form @submit.prevent="handleLogin" class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Email Address</label>
                        <input 
                            v-model="form.email" 
                            type="email" 
                            class="w-full bg-zinc-950 border border-zinc-700 rounded-lg p-3 text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition-all placeholder-zinc-600"
                            placeholder="name@company.com"
                            autocomplete="email"
                            required
                        >
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">Password</label>
                            <a href="#" class="text-xs text-emerald-500 hover:text-emerald-400 transition-colors">Forgot password?</a>
                        </div>
                        <input 
                            v-model="form.password" 
                            type="password" 
                            class="w-full bg-zinc-950 border border-zinc-700 rounded-lg p-3 text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition-all placeholder-zinc-600"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <div v-if="error" class="bg-red-500/10 border border-red-500/20 text-red-500 text-sm p-3 rounded-lg text-center font-medium">
                        {{ error }}
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-emerald-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-emerald-500 transition-all transform active:scale-[0.98] shadow-lg shadow-emerald-500/20"
                    >
                        Sign In
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-zinc-800 text-center">
                    <p class="text-sm text-zinc-500">
                        Don't have an account? 
                        <a href="/register" class="text-emerald-500 hover:text-emerald-400 font-medium hover:underline transition-colors">Register now</a>
                    </p>
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
