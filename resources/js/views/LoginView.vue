<script setup lang="ts">
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { isAxiosError } from 'axios';
import { useAuthStore } from '@/stores/auth';
import { errorMessage } from '@/services/http';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();

const email = ref('');
const password = ref('');
const showPassword = ref(false);
const loading = ref(false);
const error = ref('');
const fieldErrors = ref<Record<string, string[]>>({});

async function submit() {
    loading.value = true;
    error.value = '';
    fieldErrors.value = {};

    try {
        await auth.login(email.value, password.value);

        const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/competitions';
        router.push(redirect);
    } catch (e) {
        error.value = errorMessage(e);

        if (isAxiosError(e) && e.response?.data?.error?.details) {
            fieldErrors.value = e.response.data.error.details;
        }
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <v-main class="d-flex align-center justify-center bg-grey-lighten-4">
        <v-container class="d-flex justify-center">
            <v-card width="100%" max-width="420" class="pa-6" elevation="2">
                <div class="d-flex align-center ga-2 mb-6">
                    <v-icon icon="mdi-soccer" color="primary" size="32" />
                    <span class="text-h5 font-weight-bold">Plataforma Fútbol</span>
                </div>

                <v-alert v-if="error" type="error" variant="tonal" density="compact" class="mb-4">
                    {{ error }}
                </v-alert>

                <v-form @submit.prevent="submit">
                    <v-text-field
                        v-model="email"
                        label="Correo"
                        type="email"
                        prepend-inner-icon="mdi-email-outline"
                        autocomplete="username"
                        :error-messages="fieldErrors.email"
                        class="mb-2"
                    />

                    <v-text-field
                        v-model="password"
                        label="Contraseña"
                        :type="showPassword ? 'text' : 'password'"
                        prepend-inner-icon="mdi-lock-outline"
                        :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                        autocomplete="current-password"
                        :error-messages="fieldErrors.password"
                        class="mb-4"
                        @click:append-inner="showPassword = !showPassword"
                    />

                    <v-btn type="submit" color="primary" size="large" block :loading="loading">
                        Ingresar
                    </v-btn>
                </v-form>
            </v-card>
        </v-container>
    </v-main>
</template>
