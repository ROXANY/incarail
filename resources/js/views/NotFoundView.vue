<template>
    <v-empty-state
        icon="mdi-map-marker-question-outline"
        title="Página no encontrada"
        text="La dirección que buscas no existe."
    >
        <template #actions>
            <v-btn color="primary" variant="tonal" to="/">Ir al inicio</v-btn>
        </template>
    </v-empty-state>
</template>
