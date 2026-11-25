import axios, { isAxiosError } from 'axios';
import router from '@/router';
import { useAuthStore } from '@/stores/auth';
import { useNotify } from '@/composables/useNotify';

const http = axios.create({
    baseURL: '/api',
    headers: { Accept: 'application/json' },
});

http.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
});

http.interceptors.response.use(
    (response) => response,
    (error) => {
        const isLogin = error.config?.url === '/auth/login';

        if (error.response?.status === 401 && !isLogin) {
            const auth = useAuthStore();
            const { notify } = useNotify();

            auth.clear();
            notify('Tu sesión terminó. Vuelve a iniciar sesión.', 'warning');
            router.push({ name: 'login' });
        }

        return Promise.reject(error);
    },
);

export function errorMessage(error: unknown): string {
    if (isAxiosError(error) && error.response?.data?.error?.message) {
        return error.response.data.error.message;
    }

    return 'No se pudo conectar con el servidor.';
}

export function retryAfterSeconds(error: unknown): number | null {
    if (isAxiosError(error) && error.response?.status === 429) {
        return Number(error.response.headers['retry-after'] ?? 60);
    }

    return null;
}

export default http;
