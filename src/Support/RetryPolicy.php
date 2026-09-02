<?php

declare(strict_types=1);

namespace Clusterify\Support;

use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Configurable retry policy with exponential backoff, jitter, and RFC 7231 Retry-After header parsing.
 */
final readonly class RetryPolicy
{
    /**
     * @param int $maxRetries Maximum number of retry attempts (0 to disable retries)
     * @param int $baseDelayMs Initial delay in milliseconds for exponential backoff
     * @param int $maxDelayMs Maximum cap for backoff delay in milliseconds
     * @param float $jitter Factor between 0.0 and 1.0 to add randomness to delays
     * @param list<int> $retryableStatusCodes HTTP status codes that trigger a retry
     */
    public function __construct(
        public int $maxRetries = 3,
        public int $baseDelayMs = 500,
        public int $maxDelayMs = 10000,
        public float $jitter = 0.25,
        public array $retryableStatusCodes = [429, 500, 502, 503, 504]
    ) {}

    /**
     * Parse the RFC 7231 'Retry-After' header from an HTTP response.
     * Supports both integer seconds and HTTP-date formats.
     *
     * @param ResponseInterface $response
     * @return int|null Seconds to wait, or null if no valid header found
     */
    public static function parseRetryAfter(ResponseInterface $response): ?int
    {
        $header = $response->getHeaderLine('Retry-After');
        if ($header === '') {
            return null;
        }

        if (is_numeric($header)) {
            return max(0, (int) $header);
        }

        $timestamp = strtotime($header);
        if ($timestamp !== false) {
            return max(0, $timestamp - time());
        }

        return null;
    }

    /**
     * Determine if a failed request attempt should be retried.
     *
     * @param int $attempt Current attempt number (1-based)
     * @param int|null $statusCode HTTP status code returned (if available)
     * @param Throwable|null $exception Exception thrown (if network error)
     * @return bool
     */
    public function shouldRetry(int $attempt, ?int $statusCode, ?Throwable $exception = null): bool
    {
        if ($attempt > $this->maxRetries) {
            return false;
        }

        if ($exception !== null && $statusCode === null) {
            // Network error (timeout, connection reset, DNS failure)
            return true;
        }

        if ($statusCode !== null && in_array($statusCode, $this->retryableStatusCodes, true)) {
            return true;
        }

        return false;
    }

    /**
     * Calculate the sleep delay in milliseconds for a retry attempt.
     *
     * @param int $attempt Attempt number (1-based)
     * @param ResponseInterface|null $response Optional response to check Retry-After header
     * @return int Milliseconds to sleep
     */
    public function getDelayMs(int $attempt, ?ResponseInterface $response = null): int
    {
        if ($response !== null) {
            $retryAfterSeconds = self::parseRetryAfter($response);
            if ($retryAfterSeconds !== null) {
                return min($retryAfterSeconds * 1000, $this->maxDelayMs);
            }
        }

        // Exponential backoff: baseDelayMs * 2^(attempt - 1)
        $delay = $this->baseDelayMs * (2 ** ($attempt - 1));

        // Add randomized jitter
        if ($this->jitter > 0) {
            $jitterRange = $delay * $this->jitter;
            $delay += (int) mt_rand((int) -$jitterRange, (int) $jitterRange);
        }

        return max(0, min((int) $delay, $this->maxDelayMs));
    }
}
