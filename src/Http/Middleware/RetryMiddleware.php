<?php

declare(strict_types=1);

namespace Clusterify\Http\Middleware;

use Closure;
use Clusterify\Exceptions\ClusterifyException;
use Clusterify\Exceptions\RateLimitExceededException;
use Clusterify\Http\Request;
use Clusterify\Http\Response;
use Clusterify\Support\RetryPolicy;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Retry middleware executing exponential backoff with jitter on rate limits (429), transient 5xx, and network errors.
 */
final readonly class RetryMiddleware implements MiddlewareInterface
{
    /**
     * @param RetryPolicy $retryPolicy
     * @param LoggerInterface|null $logger
     * @param (Closure(int): void)|null $sleeper Custom sleep function for unit tests (receives ms)
     */
    public function __construct(
        private RetryPolicy $retryPolicy,
        private ?LoggerInterface $logger = null,
        private ?Closure $sleeper = null
    ) {}

    public function handle(Request $request, callable $next): Response
    {
        $attempt = 1;

        while (true) {
            try {
                $response = $next($request);

                // Support custom transport implementations that return >= 400 Response objects directly
                if ($this->retryPolicy->shouldRetry($attempt, $response->statusCode)) {
                    $delayMs = $this->retryPolicy->getDelayMs($attempt, $response->psrResponse);
                    $this->logger?->warning("Clusterify API retry attempt {$attempt} after {$delayMs}ms (HTTP {$response->statusCode}) for {$request->method} {$request->path}");

                    $this->sleep($delayMs);
                    $attempt++;
                    continue;
                }

                return $response;
            } catch (Throwable $e) {
                $statusCode = ($e instanceof ClusterifyException) ? $e->getStatusCode() : null;

                if ($this->retryPolicy->shouldRetry($attempt, $statusCode, $e)) {
                    $retryAfterSeconds = ($e instanceof RateLimitExceededException) ? $e->getRetryAfter() : null;
                    $delayMs = $this->retryPolicy->getDelayMs($attempt, retryAfterSeconds: $retryAfterSeconds);
                    $this->logger?->warning("Clusterify API retry attempt {$attempt} after {$delayMs}ms due to {$e->getMessage()} for {$request->method} {$request->path}");

                    $this->sleep($delayMs);
                    $attempt++;
                    continue;
                }

                throw $e;
            }
        }
    }

    /**
     * Sleep for given milliseconds.
     */
    private function sleep(int $milliseconds): void
    {
        if ($milliseconds <= 0) {
            return;
        }

        if ($this->sleeper !== null) {
            ($this->sleeper)($milliseconds);
            return;
        }

        usleep($milliseconds * 1000);
    }
}
