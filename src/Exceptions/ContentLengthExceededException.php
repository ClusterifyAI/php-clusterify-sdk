<?php

declare(strict_types=1);

namespace Clusterify\Exceptions;

use Throwable;

/**
 * Thrown when submitted markdown content exceeds the plan character limits (HTTP 400: CONTENT_LENGTH_EXCEEDED).
 */
class ContentLengthExceededException extends ClientException
{
    /**
     * @param string $message
     * @param array<string, mixed> $details
     * @param Throwable|null $previous
     * @param int $statusCode
     */
    public function __construct(
        string $message = 'Content character length exceeds the maximum allowed limit for your plan.',
        array $details = [],
        ?Throwable $previous = null,
        int $statusCode = 400
    ) {
        parent::__construct($message, 'CONTENT_LENGTH_EXCEEDED', $statusCode, $details, $previous);
    }
}
