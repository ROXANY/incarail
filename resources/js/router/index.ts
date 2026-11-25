import { createRouter, createWebHistory } from 'vue-router';
import ability from '@/plugins/ability';
import { useAuthStore } from '@/stores/auth';
import AppLayout from '@/layouts/AppLayout.vue';
import LoginView from '@/views/LoginView.vue';
import CompetitionsView from '@/views/CompetitionsView.vue';
import CompetitionDetailView from '@/views/CompetitionDetailView.vue';
import TeamsView from '@/views/TeamsView.vue';
import TeamDetailView from '@/views/TeamDetailView.vue';
import PlayersView from '@/views/PlayersView.vue';
import UsersView from '@/views/UsersView.vue';
import RolesView from '@/views/RolesView.vue';
import ForbiddenView from '@/views/ForbiddenView.vue';
import NotFoundView from '@/views/NotFoundView.vue';

declare module 'vue-router' {
    interface RouteMeta {
        title?: string;
        public?: boolean;
        action?: string;
        subject?: string;
    }
}

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/login', name: 'login', component: LoginView, meta: { public: true } },
        {
            path: '/',
            component: AppLayout,
            children: [
                { path: '', redirect: { name: 'competitions' } },
                {
                    path: 'competitions',
                    name: 'competitions',
                    component: CompetitionsView,
                    meta: { title: 'Competiciones', action: 'view', subject: 'competitions' },
                },
                {
                    path: 'competitions/:id',
                    name: 'competition',
                    component: CompetitionDetailView,
                    meta: { title: 'Detalle de liga', action: 'view', subject: 'competitions' },
                },
                {
                    path: 'teams',
                    name: 'teams',
                    component: TeamsView,
                    meta: { title: 'Equipos', action: 'view', subject: 'teams' },
                },
                {
                    path: 'teams/:id',
                    name: 'team',
                    component: TeamDetailView,
                    meta: { title: 'Detalle de equipo', action: 'view', subject: 'teams' },
                },
                {
                    path: 'players',
                    name: 'players',
                    component: PlayersView,
                    meta: { title: 'Jugadores', action: 'view', subject: 'players' },
                },
                {
                    path: 'admin/users',
                    name: 'users',
                    component: UsersView,
                    meta: { title: 'Usuarios', action: 'view', subject: 'users' },
                },
                {
                    path: 'admin/roles',
                    name: 'roles',
                    component: RolesView,
                    meta: { title: 'Roles y permisos', action: 'manage', subject: 'roles' },
                },
                { path: 'forbidden', name: 'forbidden', component: ForbiddenView, meta: { title: 'Acceso denegado' } },
                {
                    path: ':pathMatch(.*)*',
                    name: 'not-found',
                    component: NotFoundView,
                    meta: { title: 'Página no encontrada' },
                },
            ],
        },
    ],
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();

    if (to.meta.public) {
        if (auth.token && to.name === 'login') {
            return { name: 'competitions' };
        }

        return true;
    }

    if (!auth.token) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (!auth.user) {
        try {
            await auth.fetchMe();
        } catch {
            auth.clear();
            return { name: 'login' };
        }
    }

    if (to.meta.action && to.meta.subject && ability.cannot(to.meta.action, to.meta.subject)) {
        return { name: 'forbidden' };
    }

    return true;
});

export default router;
