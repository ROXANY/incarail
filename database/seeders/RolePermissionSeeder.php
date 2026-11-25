<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $footballPermissions = ['competitions.view', 'teams.view', 'players.view'];
        $adminPermissions = ['users.view', 'users.manage', 'roles.manage'];

        foreach (array_merge($footballPermissions, $adminPermissions) as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'api']);
        }

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api'])
            ->syncPermissions(array_merge($footballPermissions, $adminPermissions));

        Role::firstOrCreate(['name' => 'consultor', 'guard_name' => 'api'])
            ->syncPermissions($footballPermissions);
    }
}
