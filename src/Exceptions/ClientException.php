<?php

declare(strict_types=1);

namespace Clusterify\Exceptions;

use Throwable;

/**
 * Base exception for 4xx HTTP client errors.
 */
class ClientException extends ClusterifyException
{
    /**
     * @param string $message
     * @param string|null $errorCode
     * @param int|null $statusCode
     * @param array<string, mixed> $details
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message = 'HTTP Client Error',
        ?string $errorCode = null,
        ?int $statusCode = 400,
        array $details = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $errorCode, $statusCode, $details, $previous);
    }
}
