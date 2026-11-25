<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_user_can_login_and_receives_a_token(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@demo.test',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('token'));
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@demo.test',
            'password' => 'incorrecta',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('error.code', 'validation_error');
    }

    public function test_me_requires_a_token(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
        $response->assertJsonPath('error.code', 'unauthenticated');
    }

    public function test_consultor_does_not_receive_admin_rules(): void
    {
        Passport::actingAs(User::where('email', 'consultor@demo.test')->first());

        $rules = $this->getJson('/api/me')->json('data.rules');

        $this->assertContains(['action' => 'view', 'subject' => 'competitions'], $rules);
        $this->assertNotContains(['action' => 'manage', 'subject' => 'users'], $rules);
    }

    public function test_user_can_logout(): void
    {
        Passport::actingAs(User::where('email', 'admin@demo.test')->first());

        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(204);
    }
}
