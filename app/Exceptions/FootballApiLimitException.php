<?php

namespace App\Exceptions;

class FootballApiLimitException extends FootballApiException
{
    public function __construct(int $retryAfter)
    {
        parent::__construct(
            429,
            'too_many_requests',
            "Se alcanzó el límite de consultas a football-data. Intenta nuevamente en {$retryAfter} segundos.",
            ['Retry-After' => $retryAfter],
        );
    }
}
