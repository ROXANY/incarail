<?php

namespace App\Services;

use App\Exceptions\ApiException;
use Spatie\Permission\Models\Role;

class RoleService
{
    private const PROTECTED_ROLE = 'admin';

    public function create(array $data): Role
    {
        $role = Role::create(['name' => $data['name'], 'guard_name' => 'api']);
        $role->syncPermissions($data['permissions'] ?? []);

        return $role;
    }

    public function update(Role $role, array $data): Role
    {
        if ($role->name === self::PROTECTED_ROLE) {
            throw new ApiException(403, 'forbidden', 'El rol admin no se puede modificar.');
        }

        $role->name = $data['name'];
        $role->save();
        $role->syncPermissions($data['permissions'] ?? []);

        return $role;
    }

    public function delete(Role $role): void
    {
        if ($role->name === self::PROTECTED_ROLE) {
            throw new ApiException(403, 'forbidden', 'El rol admin no se puede eliminar.');
        }

        if ($role->users()->exists()) {
            throw new ApiException(422, 'role_in_use', 'No se puede eliminar un rol que tiene usuarios asignados.');
        }

        $role->delete();
    }
}
