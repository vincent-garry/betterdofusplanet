import { createRouter, createWebHistory } from 'vue-router';
import store from '../store';

// Import des vues
import Login from '../views/Login.vue';
import Dashboard from '../views/admin/Dashboard.vue';
import Quests from '../views/admin/Quests.vue';
import Dofus from '../views/admin/Dofus.vue';
import Users from '../views/admin/Users.vue';
import QuestSteps from "../views/admin/QuestSteps.vue";

const routes = [
    { path: '/login', component: Login },
    { path: '/admin', component: Dashboard, meta: { requiresAuth: true, requiresAdmin: true } },
    { path: '/admin/quests', component: Quests, meta: { requiresAuth: true, requiresAdmin: true } },
    { path: '/admin/dofus', component: Dofus, meta: { requiresAuth: true, requiresAdmin: true } },
    { path: '/admin/steps', component: QuestSteps, meta: { requiresAuth: true, requiresAdmin: true } },
    { path: '/admin/users', component: Users, meta: { requiresAuth: true, requiresAdmin: true } },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Vérification de l'authentification et des rôles
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('token');
    const user = store.state.auth?.user || null;

    if (!token && to.path !== '/login') {
        // ✅ Rediriger toutes les personnes non connectées vers /login
        next('/login');
    } else if (to.meta.requiresAdmin && (!user || !user.roles?.includes('ROLE_ADMIN'))) {
        // ✅ Si admin requis mais l'utilisateur n'est pas admin, rediriger vers /
        next('/');
    } else {
        next();
    }
});

export default router;
