<?php

declare(strict_types=1);

namespace Clusterify\Exceptions;

use Throwable;

/**
 * Thrown when trying to register a URL that already exists under another ID (HTTP 400: DUPLICATE_URL).
 */
class DuplicateUrlException extends ClientException
{
    /**
     * @param string $message
     * @param array<string, mixed> $details
     * @param Throwable|null $previous
     * @param int $statusCode
     */
    public function __construct(
        string $message = 'This URL is already registered under another ID.',
        array $details = [],
        ?Throwable $previous = null,
        int $statusCode = 400
    ) {
        parent::__construct($message, 'DUPLICATE_URL', $statusCode, $details, $previous);
    }
}
