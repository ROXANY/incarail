<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import http, { errorMessage } from '@/services/http';
import { positionLabel } from '@/utils/positions';
import type { Team } from '@/types';

const route = useRoute();

const team = ref<Team | null>(null);
const loading = ref(false);
const error = ref('');

const headers = [
    { title: 'Jugador', key: 'name' },
    { title: 'Posición', key: 'position' },
    { title: 'Camiseta', key: 'shirt_number' },
    { title: 'Nacionalidad', key: 'nationality' },
    { title: 'Nacimiento', key: 'date_of_birth' },
];

async function load() {
    loading.value = true;
    error.value = '';

    try {
        const { data } = await http.get(`/team/${route.params.id}`);

        team.value = data.data;
    } catch (e) {
        error.value = errorMessage(e);
    } finally {
        loading.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div>
        <v-btn variant="text" prepend-icon="mdi-arrow-left" to="/teams" class="mb-4">Equipos</v-btn>

        <v-card v-if="loading" variant="outlined" class="pa-6">
            <v-progress-circular indeterminate color="primary" />
        </v-card>

        <v-empty-state
            v-else-if="error"
            icon="mdi-alert-circle-outline"
            title="No se pudo cargar el equipo"
            :text="error"
        >
            <template #actions>
                <v-btn color="primary" variant="tonal" to="/teams">Volver a equipos</v-btn>
            </template>
        </v-empty-state>

        <template v-else-if="team">
            <v-card variant="outlined" class="mb-4">
                <v-card-item>
                    <template #prepend>
                        <v-avatar rounded="0" size="64">
                            <v-img v-if="team.crest" :src="team.crest" />
                            <v-icon v-else icon="mdi-shield-outline" size="40" />
                        </v-avatar>
                    </template>
                    <v-card-title class="text-h5">{{ team.name }}</v-card-title>
                    <v-card-subtitle>{{ team.area_name }}</v-card-subtitle>
                </v-card-item>

                <v-card-text>
                    <v-row dense>
                        <v-col cols="6" md="3">
                            <div class="text-caption text-medium-emphasis">Estadio</div>
                            <div>{{ team.venue ?? '—' }}</div>
                        </v-col>
                        <v-col cols="6" md="3">
                            <div class="text-caption text-medium-emphasis">Fundación</div>
                            <div>{{ team.founded ?? '—' }}</div>
                        </v-col>
                        <v-col cols="6" md="3">
                            <div class="text-caption text-medium-emphasis">Colores</div>
                            <div>{{ team.club_colors ?? '—' }}</div>
                        </v-col>
                        <v-col cols="6" md="3">
                            <div class="text-caption text-medium-emphasis">Ligas</div>
                            <div class="d-flex flex-wrap ga-1">
                                <v-chip
                                    v-for="competition in team.competitions"
                                    :key="competition.id"
                                    :to="`/competitions/${competition.id}`"
                                    size="small"
                                >
                                    {{ competition.code ?? competition.name }}
                                </v-chip>
                            </div>
                        </v-col>
                    </v-row>
                </v-card-text>
            </v-card>

            <v-card variant="outlined">
                <v-card-title>Plantilla ({{ team.players?.length ?? 0 }})</v-card-title>

                <v-data-table
                    :headers="headers"
                    :items="team.players ?? []"
                    items-per-page="25"
                    no-data-text="Este equipo no tiene jugadores registrados."
                >
                    <template #[`item.position`]="{ item }">
                        {{ positionLabel(item.position) }}
                    </template>
                    <template #[`item.shirt_number`]="{ item }">
                        {{ item.shirt_number ?? '—' }}
                    </template>
                </v-data-table>
            </v-card>
        </template>
    </div>
</template>
