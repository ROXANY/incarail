<script setup lang="ts">
import { ref } from 'vue';
import http, { errorMessage } from '@/services/http';
import { positionLabel, positions } from '@/utils/positions';
import type { Paginated, Player, TableOptions } from '@/types';

const players = ref<Player[]>([]);
const total = ref(0);
const page = ref(1);
const itemsPerPage = ref(15);
const search = ref<string | null>('');
const position = ref<string | null>(null);
const loading = ref(false);
const error = ref('');

const headers = [
    { title: 'Jugador', key: 'name', sortable: false },
    { title: 'Posición', key: 'position', sortable: false },
    { title: 'Camiseta', key: 'shirt_number', sortable: false },
    { title: 'Equipo', key: 'team', sortable: false },
    { title: 'Nacionalidad', key: 'nationality', sortable: false },
];

async function load(options?: TableOptions) {
    if (options) {
        page.value = options.page;
        itemsPerPage.value = options.itemsPerPage;
    }

    loading.value = true;
    error.value = '';

    try {
        const { data } = await http.get<Paginated<Player>>('/players', {
            params: {
                page: page.value,
                per_page: itemsPerPage.value,
                search: search.value || undefined,
                position: position.value || undefined,
            },
        });

        players.value = data.data;
        total.value = data.meta.total;
    } catch (e) {
        error.value = errorMessage(e);
    } finally {
        loading.value = false;
    }
}

function applyFilters() {
    page.value = 1;
    load();
}

function clearSearch() {
    search.value = '';
    applyFilters();
}
</script>

<template>
    <div>
        <div class="mb-4">
            <h1 class="text-h5">Jugadores</h1>
            <p class="text-medium-emphasis">Jugadores guardados de los equipos descargados.</p>
        </div>

        <v-row class="mb-2" dense>
            <v-col cols="12" md="6">
                <v-text-field
                    v-model="search"
                    placeholder="Buscar jugador"
                    prepend-inner-icon="mdi-magnify"
                    density="compact"
                    hide-details
                    clearable
                    @keyup.enter="applyFilters"
                    @click:clear="clearSearch"
                />
            </v-col>
            <v-col cols="12" md="4">
                <v-select
                    v-model="position"
                    :items="positions"
                    placeholder="Todas las posiciones"
                    density="compact"
                    hide-details
                    clearable
                    @update:model-value="applyFilters"
                />
            </v-col>
            <v-col cols="12" md="2">
                <v-btn color="primary" variant="tonal" block @click="applyFilters">Buscar</v-btn>
            </v-col>
        </v-row>

        <v-alert v-if="error" type="error" variant="tonal" class="mb-4">
            {{ error }}
            <template #append>
                <v-btn variant="text" @click="load()">Reintentar</v-btn>
            </template>
        </v-alert>

        <v-card variant="outlined">
            <v-data-table-server
                v-model:page="page"
                v-model:items-per-page="itemsPerPage"
                :headers="headers"
                :items="players"
                :items-length="total"
                :loading="loading"
                loading-text="Cargando jugadores…"
                no-data-text="Aún no hay jugadores guardados. Abre una liga para descargarlos."
                @update:options="load"
            >
                <template #[`item.position`]="{ item }">
                    {{ positionLabel(item.position) }}
                </template>
                <template #[`item.shirt_number`]="{ item }">
                    {{ item.shirt_number ?? '—' }}
                </template>
                <template #[`item.team`]="{ item }">
                    <router-link v-if="item.team" :to="`/teams/${item.team.id}`">{{ item.team.name }}</router-link>
                </template>
            </v-data-table-server>
        </v-card>
    </div>
</template>
