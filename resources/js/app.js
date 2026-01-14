import './echo';
import { createApp } from 'vue';
import LoginPage from './Pages/Auth/Login.vue';
import RegisterPage from './Pages/Auth/Register.vue';
import PixelCanvas from './Components/PixelCanvas.vue';
import PixelEditor from './Components/PixelEditor.vue';
import ProjectChat from './Components/ProjectChat.vue';

createApp({})
    .component('login-page', LoginPage)
    .component('register-page', RegisterPage)
    .component('pixel-canvas', PixelCanvas)
    .component('pixel-editor', PixelEditor)
    .component('project-chat', ProjectChat)
    .mount('#app');
