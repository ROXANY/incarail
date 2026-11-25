<script setup lang="ts">
import { useNotify } from '@/composables/useNotify';

const { messages } = useNotify();
</script>

<template>
    <v-app>
        <router-view />
        <v-snackbar-queue v-model="messages" location="top right" />
    </v-app>
</template>
