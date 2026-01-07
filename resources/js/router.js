import { createRouter, createWebHistory } from 'vue-router';
import Login from './Pages/Auth/Login.vue';
import Register from './Pages/Auth/Register.vue';
import Dashboard from './Pages/Dashboard.vue';

const routes = [
    {
        path: '/',
        redirect: '/dashboard'
    },
    {
        path: '/login',
        name: 'Login',
        component: Login,
        meta: { guest: true }
    },
    {
        path: '/register',
        name: 'Register',
        component: Register,
        meta: { guest: true }
    },
    {
        path: '/dashboard',
        name: 'Dashboard',
        component: Dashboard,
        meta: { requiresAuth: true }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Navigation Guard
router.beforeEach(async (to, from, next) => {
    const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';

    if (to.meta.requiresAuth && !isLoggedIn) {
        // Simple check for now, real verification happens via API 401 response
        next('/login');
    } else if (to.meta.guest && isLoggedIn) {
        next('/dashboard');
    } else {
        next();
    }
});

export default router;
