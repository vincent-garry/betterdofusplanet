import { createRouter, createWebHistory } from 'vue-router';
import store from '../store';

// Import des vues
import Login from '../views/Login.vue';
import Dashboard from '../views/admin/Dashboard.vue';
import Quests from '../views/admin/Quests.vue';
import Dofus from '../views/admin/Dofus.vue';
import Users from '../views/admin/Users.vue';
import QuestSteps from "../views/admin/QuestSteps.vue";
import Accueil from "@/views/Accueil.vue";
import DofusDetail from "@/views/DofusDetail.vue";
import Profile from "@/views/Profile.vue";
import Register from "@/views/Register.vue";

const routes = [
    { path: '/login', component: Login, meta: { guestOnly: true } }, // Redirige si connecté
    { path: '/admin', component: Dashboard, meta: { requiresAuth: true, requiresAdmin: true } },
    { path: '/admin/quests', component: Quests, meta: { requiresAuth: true, requiresAdmin: true } },
    { path: '/admin/dofus', component: Dofus, meta: { requiresAuth: true, requiresAdmin: true } },
    { path: '/admin/steps', component: QuestSteps, meta: { requiresAuth: true, requiresAdmin: true } },
    { path: '/admin/users', component: Users, meta: { requiresAuth: true, requiresAdmin: true } },
    { path: '/', component: Accueil, meta: { requiresAuth: false } },
    { path: '/dofus/:id', component: DofusDetail, meta: { requiresAuth: true } },
    { path: '/profile', component: Profile, meta: {requiresAuth: true} },
    { path: '/register', component: Register, meta: {requiresAuth: false} },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const user = store.state.auth?.user; // Vérifie si l'utilisateur est stocké dans Vuex
    const token = localStorage.getItem('token'); // Vérifie si un token est présent

    // Rediriger vers /login si non connecté et tente d'accéder à une page protégée
    if (to.meta.requiresAuth && !token) {
        next('/login');
    }
    // Rediriger vers / si connecté et tente d'accéder à /login
    else if (to.meta.guestOnly && token) {
        next('/');
    }
    // Vérifie les accès admin si nécessaire
    else if (to.meta.requiresAdmin && (!user || !user.roles?.includes('ROLE_ADMIN'))) {
        next('/');
    }
    else {
        next();
    }
});

export default router;
