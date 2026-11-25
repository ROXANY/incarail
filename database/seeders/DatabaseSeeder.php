<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Laravel\Passport\ClientRepository;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
        ]);

        $this->createPersonalAccessClient();
    }

    private function createPersonalAccessClient(): void
    {
        $clients = app(ClientRepository::class);

        try {
            $clients->personalAccessClient('users');
        } catch (RuntimeException) {
            $clients->createPersonalAccessGrantClient(config('app.name'), 'users');
        }
    }
}
