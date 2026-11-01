<?php

declare(strict_types=1);

namespace Clusterify\Exceptions;

use Throwable;

/**
 * Thrown when a URL's hostname does not match the chatbot's registered website domain (HTTP 400: DOMAIN_MISMATCH).
 */
class DomainMismatchException extends ClientException
{
    /**
     * @param string $message
     * @param array<string, mixed> $details
     * @param Throwable|null $previous
     * @param int $statusCode
     */
    public function __construct(
        string $message = 'The provided URL domain does not match your registered chatbot domain.',
        array $details = [],
        ?Throwable $previous = null,
        int $statusCode = 400
    ) {
        parent::__construct($message, 'DOMAIN_MISMATCH', $statusCode, $details, $previous);
    }
}
