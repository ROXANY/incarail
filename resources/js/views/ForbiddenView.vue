<template>
    <v-empty-state
        icon="mdi-lock-outline"
        title="No tienes acceso a esta sección"
        text="Tu rol no tiene permiso para ver esta página."
    >
        <template #actions>
            <v-btn color="primary" variant="tonal" to="/">Ir al inicio</v-btn>
        </template>
    </v-empty-state>
</template>
