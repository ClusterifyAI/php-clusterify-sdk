<?php

declare(strict_types=1);

namespace Clusterify\Support;

use Clusterify\Exceptions\NetworkException;
use InvalidArgumentException;
use Psr\Http\Client\ClientExceptionInterface;
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
    ) {
        if ($maxRetries < 0) {
            throw new InvalidArgumentException('RetryPolicy maxRetries must be >= 0.');
        }

        if ($baseDelayMs < 0) {
            throw new InvalidArgumentException('RetryPolicy baseDelayMs must be >= 0.');
        }

        if ($maxDelayMs < 0) {
            throw new InvalidArgumentException('RetryPolicy maxDelayMs must be >= 0.');
        }

        if ($jitter < 0.0 || $jitter > 1.0) {
            throw new InvalidArgumentException('RetryPolicy jitter must be between 0.0 and 1.0.');
        }
    }

    /**
     * Parse the RFC 7231 'Retry-After' header from an HTTP response.
     * Supports both integer seconds and HTTP-date formats.
     *
     * @param ResponseInterface $response
     * @return int|null Seconds to wait, or null if no valid header found
     */
    public static function parseRetryAfter(ResponseInterface $response): ?int
    {
        $header = trim($response->getHeaderLine('Retry-After'));
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

        if ($statusCode !== null) {
            return in_array($statusCode, $this->retryableStatusCodes, true);
        }

        if ($exception instanceof NetworkException || $exception instanceof ClientExceptionInterface) {
            return true;
        }

        return false;
    }

    /**
     * Calculate the sleep delay in milliseconds for a retry attempt.
     *
     * @param int $attempt Attempt number (1-based)
     * @param ResponseInterface|null $response Optional response to check Retry-After header
     * @param int|null $retryAfterSeconds Optional pre-parsed Retry-After value in seconds
     * @return int Milliseconds to sleep
     */
    public function getDelayMs(
        int $attempt,
        ?ResponseInterface $response = null,
        ?int $retryAfterSeconds = null
    ): int {
        $resolvedRetryAfter = $retryAfterSeconds ?? ($response !== null ? self::parseRetryAfter($response) : null);
        if ($resolvedRetryAfter !== null) {
            return min(max(0, $resolvedRetryAfter) * 1000, $this->maxDelayMs);
        }

        // Exponential backoff: baseDelayMs * 2^(attempt - 1), capped before jitter to prevent overflow & thundering herd
        $exponent = min(max(0, $attempt - 1), 30);
        $cappedDelay = min($this->maxDelayMs, $this->baseDelayMs * (1 << $exponent));

        if ($this->jitter > 0.0 && $cappedDelay > 0) {
            $jitterRange = (int) round($cappedDelay * $this->jitter);
            if ($jitterRange > 0) {
                $minDelay = max(0, $cappedDelay - $jitterRange);
                $maxDelay = min($this->maxDelayMs, $cappedDelay + $jitterRange);
                $spread = $maxDelay - $minDelay;
                if ($spread > 0) {
                    return $minDelay + random_int(0, $spread);
                }

                return $minDelay;
            }
        }

        return max(0, $cappedDelay);
    }
}
