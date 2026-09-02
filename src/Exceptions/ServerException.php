<?php

declare(strict_types=1);

namespace Clusterify\Exceptions;

use Throwable;

/**
 * Base exception for 5xx HTTP server errors.
 */
class ServerException extends ClusterifyException
{
    /**
     * @param string $message
     * @param string|null $errorCode
     * @param int|null $statusCode
     * @param array<string, mixed> $details
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message = 'HTTP Server Error',
        ?string $errorCode = 'SERVER_ERROR',
        ?int $statusCode = 500,
        array $details = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $errorCode, $statusCode, $details, $previous);
    }
}
