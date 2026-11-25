<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ApiExceptionRenderer
{
    private const MESSAGES = [
        401 => ['unauthenticated', 'Debes iniciar sesión.'],
        403 => ['forbidden', 'No tienes permiso para realizar esta acción.'],
        404 => ['not_found', 'El recurso solicitado no existe.'],
        405 => ['method_not_allowed', 'Método no permitido.'],
        429 => ['too_many_requests', 'Demasiadas solicitudes. Intenta nuevamente en unos segundos.'],
        502 => ['bad_gateway', 'El servicio externo respondió con un error.'],
        503 => ['service_unavailable', 'El servicio externo no está disponible.'],
    ];

    public function render(Throwable $e): JsonResponse
    {
        if ($e instanceof ApiException) {
            return $this->error($e->status, $e->errorCode, $e->getMessage(), headers: $e->headers);
        }

        if ($e instanceof ValidationException) {
            return $this->error(422, 'validation_error', 'Los datos enviados no son válidos.', $e->errors());
        }

        if ($e instanceof AuthenticationException) {
            return $this->error(401, ...self::MESSAGES[401]);
        }

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            [$code, $message] = self::MESSAGES[$status] ?? ['http_error', 'No se pudo completar la solicitud.'];

            return $this->error($status, $code, $message, headers: $e->getHeaders());
        }

        return $this->error(500, 'server_error', 'Ocurrió un error inesperado.');
    }

    private function error(int $status, string $code, string $message, ?array $details = null, array $headers = []): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
        ], $status, $headers);
    }
}
