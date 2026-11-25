<?php

namespace App\Exceptions;

class FootballApiUnavailableException extends FootballApiException
{
    public function __construct(int $status = 503)
    {
        $message = $status === 502
            ? 'football-data respondió con un error. Intenta más tarde.'
            : 'No se pudo conectar con football-data. Intenta más tarde.';

        parent::__construct($status, $status === 502 ? 'bad_gateway' : 'service_unavailable', $message);
    }
}
