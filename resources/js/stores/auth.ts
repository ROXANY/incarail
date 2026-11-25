import { defineStore } from 'pinia';
import { ref } from 'vue';
import http from '@/services/http';
import ability from '@/plugins/ability';
import type { User } from '@/types';

export const useAuthStore = defineStore('auth', () => {
    const token = ref<string | null>(localStorage.getItem('token'));
    const user = ref<User | null>(null);

    function setUser(newUser: User) {
        user.value = newUser;
        ability.update(newUser.rules);
    }

    async function login(email: string, password: string) {
        const { data } = await http.post('/auth/login', { email, password });

        token.value = data.token;
        localStorage.setItem('token', data.token);
        setUser(data.user);
    }

    async function fetchMe() {
        const { data } = await http.get('/me');

        setUser(data.data);
    }

    async function logout() {
        try {
            await http.post('/auth/logout');
        } finally {
            clear();
        }
    }

    function clear() {
        token.value = null;
        user.value = null;
        localStorage.removeItem('token');
        ability.update([]);
    }

    return { token, user, login, fetchMe, logout, clear };
});
