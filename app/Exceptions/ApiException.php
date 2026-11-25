<?php

namespace App\Exceptions;

use RuntimeException;

class ApiException extends RuntimeException
{
    public function __construct(
        public readonly int $status,
        public readonly string $errorCode,
        string $message,
        public readonly array $headers = [],
    ) {
        parent::__construct($message);
    }
}
