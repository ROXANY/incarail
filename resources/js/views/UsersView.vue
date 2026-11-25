<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { isAxiosError } from 'axios';
import { useAbility } from '@casl/vue';
import http, { errorMessage } from '@/services/http';
import { useNotify } from '@/composables/useNotify';
import type { AdminUser, Paginated, Role, TableOptions } from '@/types';

interface UserForm {
    name: string;
    email: string;
    password: string;
    role: string | null;
    is_active: boolean;
}

const { can } = useAbility();
const { notify } = useNotify();

const users = ref<AdminUser[]>([]);
const total = ref(0);
const page = ref(1);
const itemsPerPage = ref(15);
const search = ref<string | null>('');
const loading = ref(false);
const error = ref('');

const roles = ref<Role[]>([]);
const dialog = ref(false);
const editingId = ref<number | null>(null);
const saving = ref(false);
const formError = ref('');
const fieldErrors = ref<Record<string, string[]>>({});
const form = ref<UserForm>(emptyForm());

const headers = [
    { title: 'Nombre', key: 'name', sortable: false },
    { title: 'Correo', key: 'email', sortable: false },
    { title: 'Rol', key: 'role', sortable: false },
    { title: 'Estado', key: 'is_active', sortable: false },
    { title: '', key: 'actions', sortable: false, align: 'end' as const },
];

function emptyForm(): UserForm {
    return { name: '', email: '', password: '', role: null, is_active: true };
}

async function load(options?: TableOptions) {
    if (options) {
        page.value = options.page;
        itemsPerPage.value = options.itemsPerPage;
    }

    loading.value = true;
    error.value = '';

    try {
        const { data } = await http.get<Paginated<AdminUser>>('/users', {
            params: {
                page: page.value,
                per_page: itemsPerPage.value,
                search: search.value || undefined,
            },
        });

        users.value = data.data;
        total.value = data.meta.total;
    } catch (e) {
        error.value = errorMessage(e);
    } finally {
        loading.value = false;
    }
}

function applyFilters() {
    page.value = 1;
    load();
}

function clearSearch() {
    search.value = '';
    applyFilters();
}

async function loadRoles() {
    if (!can('manage', 'users')) {
        return;
    }

    try {
        const { data } = await http.get('/roles');

        roles.value = data.data;
    } catch {
        roles.value = [];
    }
}

function openCreate() {
    editingId.value = null;
    form.value = emptyForm();
    formError.value = '';
    fieldErrors.value = {};
    dialog.value = true;
}

function openEdit(user: AdminUser) {
    editingId.value = user.id;
    form.value = {
        name: user.name,
        email: user.email,
        password: '',
        role: user.role,
        is_active: user.is_active,
    };
    formError.value = '';
    fieldErrors.value = {};
    dialog.value = true;
}

async function save() {
    saving.value = true;
    formError.value = '';
    fieldErrors.value = {};

    try {
        if (editingId.value) {
            await http.put(`/users/${editingId.value}`, form.value);
            notify('Usuario actualizado.', 'success');
        } else {
            await http.post('/users', form.value);
            notify('Usuario creado.', 'success');
        }

        dialog.value = false;
        load();
    } catch (e) {
        formError.value = errorMessage(e);

        if (isAxiosError(e) && e.response?.data?.error?.details) {
            fieldErrors.value = e.response.data.error.details;
        }
    } finally {
        saving.value = false;
    }
}

onMounted(loadRoles);
</script>

<template>
    <div>
        <div class="d-flex flex-wrap align-center ga-4 mb-4">
            <div>
                <h1 class="text-h5">Usuarios</h1>
                <p class="text-medium-emphasis">Crea usuarios, asígnales un rol o desactívalos.</p>
            </div>
            <v-spacer />
            <v-btn v-if="can('manage', 'users')" color="primary" prepend-icon="mdi-plus" @click="openCreate">
                Nuevo usuario
            </v-btn>
        </div>

        <v-row class="mb-2" dense>
            <v-col cols="12" md="6">
                <v-text-field
                    v-model="search"
                    placeholder="Buscar por nombre o correo"
                    prepend-inner-icon="mdi-magnify"
                    density="compact"
                    hide-details
                    clearable
                    @keyup.enter="applyFilters"
                    @click:clear="clearSearch"
                />
            </v-col>
            <v-col cols="12" md="2">
                <v-btn color="primary" variant="tonal" block @click="applyFilters">Buscar</v-btn>
            </v-col>
        </v-row>

        <v-alert v-if="error" type="error" variant="tonal" class="mb-4">
            {{ error }}
            <template #append>
                <v-btn variant="text" @click="load()">Reintentar</v-btn>
            </template>
        </v-alert>

        <v-card variant="outlined">
            <v-data-table-server
                v-model:page="page"
                v-model:items-per-page="itemsPerPage"
                :headers="headers"
                :items="users"
                :items-length="total"
                :loading="loading"
                loading-text="Cargando usuarios…"
                no-data-text="No se encontraron usuarios."
                @update:options="load"
            >
                <template #[`item.role`]="{ item }">
                    <v-chip size="small">{{ item.role ?? 'Sin rol' }}</v-chip>
                </template>
                <template #[`item.is_active`]="{ item }">
                    <v-chip size="small" :color="item.is_active ? 'success' : 'grey'" variant="tonal">
                        {{ item.is_active ? 'Activo' : 'Inactivo' }}
                    </v-chip>
                </template>
                <template #[`item.actions`]="{ item }">
                    <v-btn
                        v-if="can('manage', 'users')"
                        icon="mdi-pencil-outline"
                        variant="text"
                        size="small"
                        @click="openEdit(item)"
                    />
                </template>
            </v-data-table-server>
        </v-card>

        <v-dialog v-model="dialog" max-width="520">
            <v-card :title="editingId ? 'Editar usuario' : 'Nuevo usuario'">
                <v-card-text>
                    <v-alert v-if="formError" type="error" variant="tonal" density="compact" class="mb-4">
                        {{ formError }}
                    </v-alert>

                    <v-text-field v-model="form.name" label="Nombre" :error-messages="fieldErrors.name" class="mb-2" />
                    <v-text-field
                        v-model="form.email"
                        label="Correo"
                        type="email"
                        :error-messages="fieldErrors.email"
                        class="mb-2"
                    />
                    <v-text-field
                        v-model="form.password"
                        label="Contraseña"
                        type="password"
                        :hint="editingId ? 'Déjala vacía para no cambiarla.' : 'Mínimo 8 caracteres.'"
                        persistent-hint
                        :error-messages="fieldErrors.password"
                        class="mb-2"
                    />
                    <v-select
                        v-model="form.role"
                        :items="roles"
                        item-title="name"
                        item-value="name"
                        label="Rol"
                        :error-messages="fieldErrors.role"
                        class="mb-2"
                    />
                    <v-switch
                        v-model="form.is_active"
                        label="Usuario activo"
                        color="primary"
                        :error-messages="fieldErrors.is_active"
                        hide-details="auto"
                    />
                </v-card-text>

                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" @click="dialog = false">Cancelar</v-btn>
                    <v-btn color="primary" :loading="saving" @click="save">Guardar</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>
