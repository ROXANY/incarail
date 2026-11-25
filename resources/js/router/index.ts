import { createRouter, createWebHistory } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';
import PendingView from '@/views/PendingView.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: AppLayout,
            children: [
                { path: '', redirect: { name: 'competitions' } },
                { path: 'competitions', name: 'competitions', component: PendingView, meta: { title: 'Competiciones' } },
                { path: 'teams', name: 'teams', component: PendingView, meta: { title: 'Equipos' } },
                { path: 'players', name: 'players', component: PendingView, meta: { title: 'Jugadores' } },
                { path: 'admin/users', name: 'users', component: PendingView, meta: { title: 'Usuarios' } },
                { path: 'admin/roles', name: 'roles', component: PendingView, meta: { title: 'Roles y permisos' } },
            ],
        },
    ],
});

export default router;
