<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import http, { errorMessage } from '@/services/http';
import type { Competition, Paginated, TableOptions, Team } from '@/types';

const router = useRouter();

const teams = ref<Team[]>([]);
const total = ref(0);
const page = ref(1);
const itemsPerPage = ref(15);
const search = ref<string | null>('');
const competitionId = ref<number | null>(null);
const competitions = ref<Competition[]>([]);
const loading = ref(false);
const error = ref('');

const headers = [
    { title: '', key: 'crest', sortable: false, width: 56 },
    { title: 'Equipo', key: 'name', sortable: false },
    { title: 'País', key: 'area_name', sortable: false },
    { title: 'Estadio', key: 'venue', sortable: false },
    { title: 'Jugadores', key: 'players_count', sortable: false, align: 'end' as const },
];

async function load(options?: TableOptions) {
    if (options) {
        page.value = options.page;
        itemsPerPage.value = options.itemsPerPage;
    }

    loading.value = true;
    error.value = '';

    try {
        const { data } = await http.get<Paginated<Team>>('/team', {
            params: {
                page: page.value,
                per_page: itemsPerPage.value,
                search: search.value || undefined,
                competition_id: competitionId.value || undefined,
            },
        });

        teams.value = data.data;
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

async function loadCompetitions() {
    try {
        const { data } = await http.get('/competitions');

        competitions.value = data.data;
    } catch {
        competitions.value = [];
    }
}

function openTeam(_event: Event, row: { item: Team }) {
    router.push(`/teams/${row.item.id}`);
}

onMounted(loadCompetitions);
</script>

<template>
    <div>
        <div class="mb-4">
            <h1 class="text-h5">Equipos</h1>
            <p class="text-medium-emphasis">Equipos guardados de las ligas que ya se consultaron.</p>
        </div>

        <v-row class="mb-2" dense>
            <v-col cols="12" md="6">
                <v-text-field
                    v-model="search"
                    placeholder="Buscar equipo"
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
                    v-model="competitionId"
                    :items="competitions"
                    item-title="name"
                    item-value="id"
                    placeholder="Todas las ligas"
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
                :items="teams"
                :items-length="total"
                :loading="loading"
                hover
                loading-text="Cargando equipos…"
                no-data-text="Aún no hay equipos guardados. Abre una liga para descargarlos."
                @update:options="load"
                @click:row="openTeam"
            >
                <template #[`item.crest`]="{ item }">
                    <v-avatar rounded="0" size="28">
                        <v-img v-if="item.crest" :src="item.crest" />
                    </v-avatar>
                </template>
            </v-data-table-server>
        </v-card>
    </div>
</template>
