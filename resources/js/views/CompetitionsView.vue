<script setup lang="ts">
import { onMounted, ref } from 'vue';
import http, { errorMessage, retryAfterSeconds } from '@/services/http';
import { useRetryAfter } from '@/composables/useRetryAfter';
import type { Competition } from '@/types';

const competitions = ref<Competition[]>([]);
const search = ref<string | null>('');
const loading = ref(false);
const error = ref('');
const stale = ref(false);
const { seconds, start, cancel } = useRetryAfter();

async function load() {
    loading.value = true;
    error.value = '';

    try {
        const { data } = await http.get('/competitions');

        competitions.value = data.data;
        stale.value = data.meta.stale;
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
    error.value = 'Se canceló la descarga de las ligas.';
}

function visibleCompetitions(): Competition[] {
    const text = (search.value ?? '').toLowerCase();

    return competitions.value.filter((competition) => {
        return competition.name.toLowerCase().includes(text)
            || (competition.area_name ?? '').toLowerCase().includes(text);
    });
}

onMounted(load);
</script>

<template>
    <div>
        <div class="d-flex flex-wrap align-center ga-4 mb-4">
            <div>
                <h1 class="text-h5">Competiciones</h1>
                <p class="text-medium-emphasis">Selecciona una liga para ver sus equipos y jugadores.</p>
            </div>
            <v-spacer />
            <v-text-field
                v-model="search"
                placeholder="Buscar liga o país"
                prepend-inner-icon="mdi-magnify"
                density="compact"
                hide-details
                clearable
                max-width="320"
            />
        </div>

        <v-alert v-if="stale" type="warning" variant="tonal" class="mb-4">
            football-data no respondió. Se muestran las ligas guardadas.
        </v-alert>

        <v-alert v-if="seconds > 0" type="info" variant="tonal" class="mb-4">
            Se alcanzó el límite de consultas. Reintentando en {{ seconds }} s…
            <template #append>
                <v-btn variant="text" @click="cancelRetry">Cancelar</v-btn>
            </template>
        </v-alert>

        <v-row v-if="loading && competitions.length === 0">
            <v-col v-for="n in 6" :key="n" cols="12" sm="6" lg="4">
                <v-skeleton-loader type="list-item-avatar-two-line" />
            </v-col>
        </v-row>

        <v-empty-state
            v-else-if="error"
            icon="mdi-alert-circle-outline"
            title="No se pudieron cargar las ligas"
            :text="error"
        >
            <template #actions>
                <v-btn color="primary" variant="tonal" @click="load">Reintentar</v-btn>
            </template>
        </v-empty-state>

        <v-empty-state
            v-else-if="seconds === 0 && visibleCompetitions().length === 0"
            icon="mdi-trophy-outline"
            title="No hay ligas para mostrar"
            text="Prueba con otro texto de búsqueda."
        />

        <v-row v-else>
            <v-col v-for="competition in visibleCompetitions()" :key="competition.id" cols="12" sm="6" lg="4">
                <v-card :to="`/competitions/${competition.id}`" variant="outlined" class="h-100">
                    <v-card-item>
                        <template #prepend>
                            <v-avatar rounded="0" size="48">
                                <v-img v-if="competition.emblem" :src="competition.emblem" />
                                <v-icon v-else icon="mdi-trophy-outline" />
                            </v-avatar>
                        </template>
                        <v-card-title>{{ competition.name }}</v-card-title>
                        <v-card-subtitle>{{ competition.area_name }}</v-card-subtitle>
                    </v-card-item>
                    <v-card-text class="d-flex flex-wrap ga-2">
                        <v-chip v-if="competition.code" size="small">{{ competition.code }}</v-chip>
                        <v-chip v-if="competition.season_start" size="small" variant="outlined">
                            Temporada {{ competition.season_start.slice(0, 4) }}
                        </v-chip>
                        <v-chip v-if="competition.teams_synced_at" size="small" color="primary" variant="tonal">
                            Equipos descargados
                        </v-chip>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </div>
</template>
