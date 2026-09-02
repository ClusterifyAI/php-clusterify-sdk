<?php

declare(strict_types=1);

namespace Clusterify\Exceptions;

use Throwable;

/**
 * Thrown when API rate limits are exceeded (HTTP 429: RATE_LIMIT_EXCEEDED).
 */
class RateLimitExceededException extends ClientException
{
    /**
     * @param string $message
     * @param int|null $retryAfter Number of seconds to wait before retrying, if indicated by header/payload
     * @param array<string, mixed> $details
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message = 'API rate limit exceeded. Please throttle your requests.',
        protected readonly ?int $retryAfter = null,
        array $details = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 'RATE_LIMIT_EXCEEDED', 429, $details, $previous);
    }

    /**
     * Get the number of seconds to wait before retrying (from Retry-After header or null).
     */
    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
