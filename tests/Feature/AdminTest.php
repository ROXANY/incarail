<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_consultor_cannot_list_users(): void
    {
        Passport::actingAs(User::where('email', 'consultor@demo.test')->first());

        $response = $this->getJson('/api/users');

        $response->assertStatus(403);
        $response->assertJsonPath('error.code', 'forbidden');
    }

    public function test_admin_can_create_a_user_with_a_role(): void
    {
        Passport::actingAs(User::where('email', 'admin@demo.test')->first());

        $response = $this->postJson('/api/users', [
            'name' => 'Nuevo Usuario',
            'email' => 'nuevo@demo.test',
            'password' => 'password123',
            'role' => 'consultor',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.role', 'consultor');
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $consultor = User::where('email', 'consultor@demo.test')->first();
        Passport::actingAs(User::where('email', 'admin@demo.test')->first());

        $this->putJson("/api/users/{$consultor->id}", [
            'name' => $consultor->name,
            'email' => $consultor->email,
            'role' => 'consultor',
            'is_active' => false,
        ])->assertStatus(200);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'consultor@demo.test',
            'password' => 'password',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_create_a_role_with_permissions(): void
    {
        Passport::actingAs(User::where('email', 'admin@demo.test')->first());

        $response = $this->postJson('/api/roles', [
            'name' => 'analista',
            'permissions' => ['competitions.view', 'teams.view'],
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.permissions', ['competitions.view', 'teams.view']);
    }

    public function test_admin_role_cannot_be_deleted(): void
    {
        Passport::actingAs(User::where('email', 'admin@demo.test')->first());
        $admin = Role::where('name', 'admin')->first();

        $response = $this->deleteJson("/api/roles/{$admin->id}");

        $response->assertStatus(403);
    }
}
