<?php

namespace App\Contracts;

interface FootballApiInterface
{
    public function competitions(): array;

    public function competitionTeams(int $competitionId): array;
}
