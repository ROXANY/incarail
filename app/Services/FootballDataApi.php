<?php

namespace App\Services;

use App\Contracts\FootballApiInterface;
use App\Exceptions\FootballApiLimitException;
use App\Exceptions\FootballApiUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

class FootballDataApi implements FootballApiInterface
{
    private const LIMITER_KEY = 'football-data';

    public function competitions(): array
    {
        return $this->get('/competitions');
    }

    public function competitionTeams(int $competitionId): array
    {
        return $this->get("/competitions/{$competitionId}/teams");
    }

    private function get(string $path): array
    {
        $maxRequests = config('services.football_data.max_requests_per_minute');

        if (RateLimiter::tooManyAttempts(self::LIMITER_KEY, $maxRequests)) {
            throw new FootballApiLimitException(RateLimiter::availableIn(self::LIMITER_KEY));
        }

        RateLimiter::hit(self::LIMITER_KEY, 60);

        try {
            $response = Http::baseUrl(config('services.football_data.url'))
                ->withHeaders(['X-Auth-Token' => config('services.football_data.token')])
                ->timeout(10)
                ->get($path);
        } catch (ConnectionException) {
            throw new FootballApiUnavailableException(503);
        }

        if ($response->status() === 429) {
            throw new FootballApiLimitException((int) $response->header('X-RequestCounter-Reset') ?: 60);
        }

        if ($response->failed()) {
            throw new FootballApiUnavailableException(502);
        }

        return $response->json();
    }
}
