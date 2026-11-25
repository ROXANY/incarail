<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { isAxiosError } from 'axios';
import http, { errorMessage } from '@/services/http';
import { useNotify } from '@/composables/useNotify';
import { permissionLabel } from '@/utils/permissions';
import type { Role } from '@/types';

interface RoleForm {
    name: string;
    permissions: string[];
}

const { notify } = useNotify();

const roles = ref<Role[]>([]);
const permissions = ref<string[]>([]);
const loading = ref(false);
const error = ref('');

const dialog = ref(false);
const editingId = ref<number | null>(null);
const saving = ref(false);
const formError = ref('');
const fieldErrors = ref<Record<string, string[]>>({});
const form = ref<RoleForm>({ name: '', permissions: [] });

const deleteDialog = ref(false);
const roleToDelete = ref<Role | null>(null);
const deleting = ref(false);

const headers = [
    { title: 'Rol', key: 'name' },
    { title: 'Permisos', key: 'permissions', sortable: false },
    { title: 'Usuarios', key: 'users_count', align: 'end' as const },
    { title: '', key: 'actions', sortable: false, align: 'end' as const },
];

async function load() {
    loading.value = true;
    error.value = '';

    try {
        const rolesResponse = await http.get('/roles');
        const permissionsResponse = await http.get('/permissions');

        roles.value = rolesResponse.data.data;
        permissions.value = permissionsResponse.data.data;
    } catch (e) {
        error.value = errorMessage(e);
    } finally {
        loading.value = false;
    }
}

function openCreate() {
    editingId.value = null;
    form.value = { name: '', permissions: [] };
    formError.value = '';
    fieldErrors.value = {};
    dialog.value = true;
}

function openEdit(role: Role) {
    editingId.value = role.id;
    form.value = { name: role.name, permissions: [...role.permissions] };
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
            await http.put(`/roles/${editingId.value}`, form.value);
            notify('Rol actualizado.', 'success');
        } else {
            await http.post('/roles', form.value);
            notify('Rol creado.', 'success');
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

function confirmDelete(role: Role) {
    roleToDelete.value = role;
    deleteDialog.value = true;
}

async function deleteRole() {
    if (!roleToDelete.value) {
        return;
    }

    deleting.value = true;

    try {
        await http.delete(`/roles/${roleToDelete.value.id}`);
        notify('Rol eliminado.', 'success');
        deleteDialog.value = false;
        load();
    } catch (e) {
        notify(errorMessage(e), 'error');
    } finally {
        deleting.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div>
        <div class="d-flex flex-wrap align-center ga-4 mb-4">
            <div>
                <h1 class="text-h5">Roles y permisos</h1>
                <p class="text-medium-emphasis">Define qué puede ver o gestionar cada rol.</p>
            </div>
            <v-spacer />
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openCreate">Nuevo rol</v-btn>
        </div>

        <v-alert v-if="error" type="error" variant="tonal" class="mb-4">
            {{ error }}
            <template #append>
                <v-btn variant="text" @click="load">Reintentar</v-btn>
            </template>
        </v-alert>

        <v-card variant="outlined">
            <v-data-table
                :headers="headers"
                :items="roles"
                :loading="loading"
                loading-text="Cargando roles…"
                no-data-text="No hay roles registrados."
                hide-default-footer
            >
                <template #[`item.permissions`]="{ item }">
                    <div class="d-flex flex-wrap ga-1 py-2">
                        <v-chip v-for="permission in item.permissions" :key="permission" size="small">
                            {{ permissionLabel(permission) }}
                        </v-chip>
                    </div>
                </template>
                <template #[`item.actions`]="{ item }">
                    <template v-if="item.name !== 'admin'">
                        <v-btn icon="mdi-pencil-outline" variant="text" size="small" @click="openEdit(item)" />
                        <v-btn
                            icon="mdi-delete-outline"
                            variant="text"
                            size="small"
                            color="error"
                            @click="confirmDelete(item)"
                        />
                    </template>
                    <v-chip v-else size="small" variant="outlined">Protegido</v-chip>
                </template>
            </v-data-table>
        </v-card>

        <v-dialog v-model="dialog" max-width="520">
            <v-card :title="editingId ? 'Editar rol' : 'Nuevo rol'">
                <v-card-text>
                    <v-alert v-if="formError" type="error" variant="tonal" density="compact" class="mb-4">
                        {{ formError }}
                    </v-alert>

                    <v-text-field v-model="form.name" label="Nombre del rol" :error-messages="fieldErrors.name" />

                    <div class="text-subtitle-2 mt-2">Permisos</div>
                    <v-checkbox
                        v-for="permission in permissions"
                        :key="permission"
                        v-model="form.permissions"
                        :value="permission"
                        :label="permissionLabel(permission)"
                        density="compact"
                        hide-details
                    />
                </v-card-text>

                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" @click="dialog = false">Cancelar</v-btn>
                    <v-btn color="primary" :loading="saving" @click="save">Guardar</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="deleteDialog" max-width="420">
            <v-card title="Eliminar rol">
                <v-card-text>
                    ¿Seguro que quieres eliminar el rol <strong>{{ roleToDelete?.name }}</strong>?
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" @click="deleteDialog = false">Cancelar</v-btn>
                    <v-btn color="error" :loading="deleting" @click="deleteRole">Eliminar</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>
