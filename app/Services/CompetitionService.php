<?php

namespace App\Services;

use App\Contracts\FootballApiInterface;
use App\Exceptions\ApiException;
use App\Exceptions\FootballApiException;
use App\Models\Competition;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CompetitionService
{
    public function __construct(private FootballApiInterface $footballApi) {}

    public function list(): array
    {
        $stale = false;

        if ($this->listNeedsSync()) {
            try {
                $this->saveCompetitions($this->footballApi->competitions());
            } catch (FootballApiException $e) {
                if (Competition::count() === 0) {
                    throw $e;
                }

                $stale = true;
            }
        }

        $competitions = Competition::orderBy('name')->get();

        return [
            'competitions' => $competitions,
            'stale' => $stale,
        ];
    }

    public function detail(Competition $competition): Competition
    {
        if ($competition->teams_synced_at === null) {
            $this->downloadTeamsOnce($competition);
        }

        $competition->load(['teams' => function ($query) {
            $query->withCount('players')->orderBy('name');
        }]);

        return $competition;
    }

    private function downloadTeamsOnce(Competition $competition): void
    {
        $lock = Cache::lock("competition-teams-{$competition->id}", 30);

        try {
            $lock->block(20, function () use ($competition) {
                $competition->refresh();

                if ($competition->teams_synced_at === null) {
                    $data = $this->footballApi->competitionTeams($competition->external_id);
                    $this->saveTeams($competition, $data);
                }
            });
        } catch (LockTimeoutException) {
            throw new ApiException(503, 'service_unavailable', 'La liga se está descargando. Intenta nuevamente en unos segundos.');
        }
    }

    private function listNeedsSync(): bool
    {
        $lastUpdate = Competition::max('updated_at');

        return $lastUpdate === null || now()->subHours($this->ttlHours())->greaterThan($lastUpdate);
    }

    private function ttlHours(): int
    {
        return config('services.football_data.ttl_hours');
    }

    private function saveCompetitions(array $data): void
    {
        $rows = [];

        foreach ($data['competitions'] ?? [] as $item) {
            $rows[] = [
                'external_id' => $item['id'],
                'code' => $item['code'] ?? null,
                'name' => $item['name'],
                'type' => $item['type'] ?? null,
                'area_name' => $item['area']['name'] ?? null,
                'emblem' => $item['emblem'] ?? null,
                'season_start' => $item['currentSeason']['startDate'] ?? null,
                'season_end' => $item['currentSeason']['endDate'] ?? null,
            ];
        }

        Competition::upsert(
            $rows,
            ['external_id'],
            ['code', 'name', 'type', 'area_name', 'emblem', 'season_start', 'season_end'],
        );
    }

    private function saveTeams(Competition $competition, array $data): void
    {
        DB::transaction(function () use ($competition, $data) {
            $teamIds = [];

            foreach ($data['teams'] ?? [] as $item) {
                $team = Team::updateOrCreate(
                    ['external_id' => $item['id']],
                    [
                        'name' => $item['name'],
                        'short_name' => $item['shortName'] ?? null,
                        'tla' => $item['tla'] ?? null,
                        'crest' => $item['crest'] ?? null,
                        'venue' => $item['venue'] ?? null,
                        'founded' => $item['founded'] ?? null,
                        'club_colors' => $item['clubColors'] ?? null,
                        'area_name' => $item['area']['name'] ?? null,
                    ],
                );

                $this->savePlayers($team, $item['squad'] ?? []);

                $teamIds[] = $team->id;
            }

            $competition->teams()->sync($teamIds);

            $competition->update([
                'season_start' => $data['season']['startDate'] ?? $competition->season_start,
                'season_end' => $data['season']['endDate'] ?? $competition->season_end,
                'teams_synced_at' => now(),
            ]);
        });
    }

    private function savePlayers(Team $team, array $squad): void
    {
        $rows = [];

        foreach ($squad as $item) {
            $rows[] = [
                'external_id' => $item['id'],
                'team_id' => $team->id,
                'name' => $item['name'],
                'position' => $item['position'] ?? null,
                'shirt_number' => $item['shirtNumber'] ?? null,
                'date_of_birth' => $item['dateOfBirth'] ?? null,
                'nationality' => $item['nationality'] ?? null,
            ];
        }

        Player::upsert(
            $rows,
            ['external_id'],
            ['team_id', 'name', 'position', 'shirt_number', 'date_of_birth', 'nationality'],
        );
    }
}
