import { createApp } from 'vue';
import LoginPage from './Pages/Auth/Login.vue';
import RegisterPage from './Pages/Auth/Register.vue';
import PixelCanvas from './Components/PixelCanvas.vue';

createApp({})
    .component('login-page', LoginPage)
    .component('register-page', RegisterPage)
    .component('pixel-canvas', PixelCanvas)
    .mount('#app');
