<script setup lang="ts">
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAbility } from '@casl/vue';
import { useAuthStore } from '@/stores/auth';

interface MenuItem {
    title: string;
    icon: string;
    to: string;
    action: string;
    subject: string;
}

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const { can } = useAbility();
const drawer = ref<boolean | null>(null);

const footballItems: MenuItem[] = [
    { title: 'Competiciones', icon: 'mdi-trophy-outline', to: '/competitions', action: 'view', subject: 'competitions' },
    { title: 'Equipos', icon: 'mdi-shield-outline', to: '/teams', action: 'view', subject: 'teams' },
    { title: 'Jugadores', icon: 'mdi-account-group-outline', to: '/players', action: 'view', subject: 'players' },
];

const adminItems: MenuItem[] = [
    { title: 'Usuarios', icon: 'mdi-account-cog-outline', to: '/admin/users', action: 'view', subject: 'users' },
    { title: 'Roles y permisos', icon: 'mdi-shield-key-outline', to: '/admin/roles', action: 'manage', subject: 'roles' },
];

function initials(name: string): string {
    const words = name.split(' ');
    let result = '';

    for (const word of words.slice(0, 2)) {
        result += word.charAt(0).toUpperCase();
    }

    return result;
}

async function logout() {
    await auth.logout();
    router.push({ name: 'login' });
}
</script>

<template>
    <v-navigation-drawer v-model="drawer" mobile-breakpoint="md">
        <div class="d-flex align-center ga-2 px-4 py-5">
            <v-icon icon="mdi-soccer" color="primary" size="28" />
            <span class="text-h6 font-weight-bold">Plataforma Fútbol</span>
        </div>

        <v-divider />

        <v-list nav density="comfortable" color="primary">
            <template v-for="item in footballItems" :key="item.to">
                <v-list-item
                    v-if="can(item.action, item.subject)"
                    :to="item.to"
                    :prepend-icon="item.icon"
                    :title="item.title"
                />
            </template>

            <template v-if="can('view', 'users') || can('manage', 'roles')">
                <v-list-subheader class="mt-2">Administración</v-list-subheader>

                <template v-for="item in adminItems" :key="item.to">
                    <v-list-item
                        v-if="can(item.action, item.subject)"
                        :to="item.to"
                        :prepend-icon="item.icon"
                        :title="item.title"
                    />
                </template>
            </template>
        </v-list>
    </v-navigation-drawer>

    <v-app-bar flat border="b">
        <v-app-bar-nav-icon class="d-md-none" @click="drawer = !drawer" />
        <v-app-bar-title>{{ route.meta.title }}</v-app-bar-title>

        <template #append>
            <v-menu v-if="auth.user">
                <template #activator="{ props }">
                    <v-btn v-bind="props" variant="text" class="text-none">
                        <v-avatar color="primary" size="32" class="mr-sm-2">
                            <span class="text-caption font-weight-bold">{{ initials(auth.user.name) }}</span>
                        </v-avatar>
                        <span class="d-none d-sm-inline">{{ auth.user.name }}</span>
                        <v-icon icon="mdi-chevron-down" end />
                    </v-btn>
                </template>

                <v-list density="compact">
                    <v-list-item :subtitle="auth.user.email" :title="auth.user.roles.join(', ')" />
                    <v-divider />
                    <v-list-item prepend-icon="mdi-logout" title="Cerrar sesión" @click="logout" />
                </v-list>
            </v-menu>
        </template>
    </v-app-bar>

    <v-main>
        <v-container fluid class="pa-4 pa-md-6">
            <router-view />
        </v-container>
    </v-main>
</template>
