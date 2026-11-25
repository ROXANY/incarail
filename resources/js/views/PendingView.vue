<script setup lang="ts">
import { useRoute } from 'vue-router';

const route = useRoute();
</script>

<template>
    <v-card variant="outlined" class="pa-6 text-center text-medium-emphasis">
        {{ route.meta.title }} · vista pendiente
    </v-card>
</template>
