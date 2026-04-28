<?php

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Réponse API standardisée pour tous les endpoints.
 *
 * Format cohérent :
 *   {
 *     "success": true|false,
 *     "data": {...},
 *     "error": null|"message d'erreur",
 *     "meta": {"page": 1, "total": 100}
 *   }
 */
class ApiResponse extends JsonResponse
{
    public function __construct(
        mixed $data = null,
        int $status = 200,
        array $headers = [],
        array $meta = [],
        ?string $error = null,
    ) {
        $body = [
            'success' => $error === null,
            'data'    => $data,
            'error'   => $error,
        ];

        if (!empty($meta)) {
            $body['meta'] = $meta;
        }

        parent::__construct($body, $status, $headers);
    }

    public static function ok(mixed $data = null, array $meta = []): self
    {
        return new self(data: $data, meta: $meta);
    }

    public static function created(mixed $data = null): self
    {
        return new self(data: $data, status: 201);
    }

    public static function error(string $message, int $status = 400): self
    {
        return new self(status: $status, error: $message);
    }

    public static function notFound(string $message = 'Ressource introuvable.'): self
    {
        return new self(status: 404, error: $message);
    }

    public static function forbidden(string $message = 'Accès refusé.'): self
    {
        return new self(status: 403, error: $message);
    }

    public static function conflict(string $message): self
    {
        return new self(status: 409, error: $message);
    }

    public static function unprocessable(string $message): self
    {
        return new self(status: 422, error: $message);
    }

    public static function done(string $status = 'done', mixed $data = null): self
    {
        $payload = array_merge(['status' => $status], (array) $data);
        return new self(data: $payload);
    }
}
