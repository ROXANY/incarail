<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Passport\Passport;
use Tests\TestCase;

class FootballTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        Passport::actingAs(User::where('email', 'consultor@demo.test')->first());
    }

    public function test_competitions_are_loaded_from_football_data_only_the_first_time(): void
    {
        Http::fake([
            'api.football-data.org/v4/competitions' => Http::response([
                'competitions' => [
                    ['id' => 2021, 'code' => 'PL', 'name' => 'Premier League', 'area' => ['name' => 'England']],
                ],
            ]),
        ]);

        $this->getJson('/api/competitions')->assertStatus(200);
        $this->getJson('/api/competitions')->assertStatus(200);

        $this->assertDatabaseCount('competitions', 1);
        Http::assertSentCount(1);
    }

    public function test_team_shared_by_two_competitions_is_not_duplicated(): void
    {
        $premier = Competition::create(['external_id' => 2021, 'name' => 'Premier League']);
        $champions = Competition::create(['external_id' => 2001, 'name' => 'UEFA Champions League']);

        Http::fake([
            'api.football-data.org/v4/competitions/2021/teams' => Http::response([
                'teams' => [
                    [
                        'id' => 57,
                        'name' => 'Arsenal FC',
                        'squad' => [
                            ['id' => 1, 'name' => 'Jugador Uno', 'position' => 'Goalkeeper'],
                            ['id' => 2, 'name' => 'Jugador Dos', 'position' => 'Defence'],
                        ],
                    ],
                ],
            ]),
            'api.football-data.org/v4/competitions/2001/teams' => Http::response([
                'teams' => [
                    [
                        'id' => 57,
                        'name' => 'Arsenal FC',
                        'squad' => [
                            ['id' => 1, 'name' => 'Jugador Uno', 'position' => 'Goalkeeper'],
                        ],
                    ],
                ],
            ]),
        ]);

        $this->getJson("/api/competitions/{$premier->id}")->assertStatus(200);
        $this->getJson("/api/competitions/{$champions->id}")->assertStatus(200);

        $this->assertDatabaseCount('teams', 1);
        $this->assertDatabaseCount('players', 2);
        $this->assertDatabaseCount('competition_team', 2);
    }

    public function test_competition_detail_returns_503_when_football_data_is_down(): void
    {
        $competition = Competition::create(['external_id' => 2021, 'name' => 'Premier League']);

        Http::fake(function () {
            throw new ConnectionException('Sin conexión');
        });

        $response = $this->getJson("/api/competitions/{$competition->id}");

        $response->assertStatus(503);
        $response->assertJsonPath('error.code', 'service_unavailable');
    }

    public function test_competition_detail_returns_429_when_limit_is_reached(): void
    {
        $competition = Competition::create(['external_id' => 2021, 'name' => 'Premier League']);

        for ($i = 0; $i < 9; $i++) {
            RateLimiter::hit('football-data', 60);
        }

        $response = $this->getJson("/api/competitions/{$competition->id}");

        $response->assertStatus(429);
        $response->assertHeader('Retry-After');
    }

    public function test_downloaded_competition_does_not_call_football_data_again(): void
    {
        $competition = Competition::create([
            'external_id' => 2021,
            'name' => 'Premier League',
            'teams_synced_at' => now()->subDays(30),
        ]);

        Http::fake();

        $response = $this->getJson("/api/competitions/{$competition->id}");

        $response->assertStatus(200);
        Http::assertNothingSent();
    }

    public function test_team_that_does_not_exist_returns_404(): void
    {
        $response = $this->getJson('/api/team/999999');

        $response->assertStatus(404);
        $response->assertJsonPath('error.code', 'not_found');
    }
}
