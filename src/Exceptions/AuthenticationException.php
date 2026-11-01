<?php

declare(strict_types=1);

namespace Clusterify\Exceptions;

use Throwable;

/**
 * Thrown when credentials are missing or invalid (HTTP 401: MISSING_API_KEYS, INVALID_API_KEYS).
 */
class AuthenticationException extends ClientException
{
    /**
     * @param string $message
     * @param string $errorCode
     * @param array<string, mixed> $details
     * @param Throwable|null $previous
     * @param int $statusCode
     */
    public function __construct(
        string $message = 'Authentication failed. Please verify your public and secret keys.',
        string $errorCode = 'INVALID_API_KEYS',
        array $details = [],
        ?Throwable $previous = null,
        int $statusCode = 401
    ) {
        parent::__construct($message, $errorCode, $statusCode, $details, $previous);
    }
}
