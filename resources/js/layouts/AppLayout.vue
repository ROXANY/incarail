<script setup lang="ts">
import { computed, ref } from 'vue';
import { useRoute } from 'vue-router';

interface MenuItem {
    title: string;
    icon: string;
    to: string;
}

const route = useRoute();
const drawer = ref<boolean | null>(null);

const pageTitle = computed(() => String(route.meta.title ?? ''));

const footballItems: MenuItem[] = [
    { title: 'Competiciones', icon: 'mdi-trophy-outline', to: '/competitions' },
    { title: 'Equipos', icon: 'mdi-shield-outline', to: '/teams' },
    { title: 'Jugadores', icon: 'mdi-account-group-outline', to: '/players' },
];

const adminItems: MenuItem[] = [
    { title: 'Usuarios', icon: 'mdi-account-cog-outline', to: '/admin/users' },
    { title: 'Roles y permisos', icon: 'mdi-shield-key-outline', to: '/admin/roles' },
];
</script>

<template>
    <v-navigation-drawer v-model="drawer" mobile-breakpoint="md">
        <div class="d-flex align-center ga-2 px-4 py-5">
            <v-icon icon="mdi-soccer" color="primary" size="28" />
            <span class="text-h6 font-weight-bold">Plataforma Fútbol</span>
        </div>

        <v-divider />

        <v-list nav density="comfortable" color="primary">
            <v-list-item
                v-for="item in footballItems"
                :key="item.to"
                :to="item.to"
                :prepend-icon="item.icon"
                :title="item.title"
            />

            <v-list-subheader class="mt-2">Administración</v-list-subheader>

            <v-list-item
                v-for="item in adminItems"
                :key="item.to"
                :to="item.to"
                :prepend-icon="item.icon"
                :title="item.title"
            />
        </v-list>
    </v-navigation-drawer>

    <v-app-bar flat border="b">
        <v-app-bar-nav-icon class="d-md-none" @click="drawer = !drawer" />
        <v-app-bar-title>{{ pageTitle }}</v-app-bar-title>

        <template #append>
            <v-menu>
                <template #activator="{ props }">
                    <v-btn v-bind="props" variant="text" class="text-none">
                        <v-avatar color="primary" size="32" class="mr-sm-2">
                            <span class="text-caption font-weight-bold">AD</span>
                        </v-avatar>
                        <span class="d-none d-sm-inline">Admin Demo</span>
                        <v-icon icon="mdi-chevron-down" end />
                    </v-btn>
                </template>

                <v-list density="compact">
                    <v-list-item prepend-icon="mdi-logout" title="Cerrar sesión" />
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
