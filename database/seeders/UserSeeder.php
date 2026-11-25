<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@demo.test'],
            ['name' => 'Admin Demo', 'password' => 'password', 'is_active' => true],
        );
        $admin->syncRoles('admin');

        $consultor = User::updateOrCreate(
            ['email' => 'consultor@demo.test'],
            ['name' => 'Consultor Demo', 'password' => 'password', 'is_active' => true],
        );
        $consultor->syncRoles('consultor');
    }
}
