<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import http, { errorMessage, retryAfterSeconds } from '@/services/http';
import { useRetryAfter } from '@/composables/useRetryAfter';
import type { Competition, Team } from '@/types';

const route = useRoute();
const router = useRouter();

const competition = ref<Competition | null>(null);
const loading = ref(false);
const error = ref('');
const { seconds, start, cancel } = useRetryAfter();

const headers = [
    { title: '', key: 'crest', sortable: false, width: 56 },
    { title: 'Equipo', key: 'name' },
    { title: 'Sigla', key: 'tla' },
    { title: 'Estadio', key: 'venue' },
    { title: 'Fundación', key: 'founded' },
    { title: 'Jugadores', key: 'players_count', align: 'end' as const },
];

async function load() {
    loading.value = true;
    error.value = '';

    try {
        const { data } = await http.get(`/competitions/${route.params.id}`);

        competition.value = data.data;
    } catch (e) {
        const wait = retryAfterSeconds(e);

        if (wait) {
            start(wait, load);
        } else {
            error.value = errorMessage(e);
        }
    } finally {
        loading.value = false;
    }
}

function cancelRetry() {
    cancel();
    error.value = 'Se canceló la descarga de la liga.';
}

function openTeam(_event: Event, row: { item: Team }) {
    router.push(`/teams/${row.item.id}`);
}

onMounted(load);
</script>

<template>
    <div>
        <v-btn variant="text" prepend-icon="mdi-arrow-left" to="/competitions" class="mb-4">Competiciones</v-btn>

        <v-alert v-if="seconds > 0" type="info" variant="tonal" class="mb-4">
            La liga se está descargando y se alcanzó el límite de consultas. Reintentando en {{ seconds }} s…
            <template #append>
                <v-btn variant="text" @click="cancelRetry">Cancelar</v-btn>
            </template>
        </v-alert>

        <v-card v-if="loading" variant="outlined" class="pa-6">
            <div class="d-flex align-center ga-4">
                <v-progress-circular indeterminate color="primary" />
                <span>Cargando la liga. Si es la primera vez, puede tardar unos segundos…</span>
            </div>
        </v-card>

        <v-empty-state
            v-else-if="error"
            icon="mdi-alert-circle-outline"
            title="No se pudo cargar la liga"
            :text="error"
        >
            <template #actions>
                <v-btn color="primary" variant="tonal" @click="load">Reintentar</v-btn>
            </template>
        </v-empty-state>

        <template v-else-if="competition">
            <v-card variant="outlined" class="mb-4">
                <v-card-item>
                    <template #prepend>
                        <v-avatar rounded="0" size="64">
                            <v-img v-if="competition.emblem" :src="competition.emblem" />
                            <v-icon v-else icon="mdi-trophy-outline" size="40" />
                        </v-avatar>
                    </template>
                    <v-card-title class="text-h5">{{ competition.name }}</v-card-title>
                    <v-card-subtitle>
                        {{ competition.area_name }}
                        <span v-if="competition.season_start">
                            · Temporada {{ competition.season_start }} a {{ competition.season_end }}
                        </span>
                    </v-card-subtitle>
                </v-card-item>
            </v-card>

            <v-card variant="outlined">
                <v-card-title>Equipos ({{ competition.teams?.length ?? 0 }})</v-card-title>

                <v-data-table
                    :headers="headers"
                    :items="competition.teams ?? []"
                    items-per-page="25"
                    hover
                    no-data-text="Esta liga no tiene equipos registrados."
                    @click:row="openTeam"
                >
                    <template #[`item.crest`]="{ item }">
                        <v-avatar rounded="0" size="28">
                            <v-img v-if="item.crest" :src="item.crest" />
                        </v-avatar>
                    </template>
                </v-data-table>
            </v-card>
        </template>
    </div>
</template>
