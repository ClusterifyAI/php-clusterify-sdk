<?php

declare(strict_types=1);

namespace Clusterify\Http\Middleware;

use Clusterify\Exceptions\ClusterifyException;
use Clusterify\Exceptions\RateLimitExceededException;
use Clusterify\Exceptions\ServerException;
use Clusterify\Http\Request;
use Clusterify\Http\Response;
use Clusterify\Support\RetryPolicy;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Retry middleware executing exponential backoff with jitter on rate limits (429) and transient 5xx errors.
 */
final readonly class RetryMiddleware implements MiddlewareInterface
{
    /**
     * @param RetryPolicy $retryPolicy
     * @param LoggerInterface|null $logger
     * @param (callable(int): void)|null $sleeper Custom sleep function for unit tests (receives ms)
     */
    public function __construct(
        private RetryPolicy $retryPolicy,
        private ?LoggerInterface $logger = null,
        private mixed $sleeper = null
    ) {}

    public function handle(Request $request, callable $next): Response
    {
        $attempt = 1;

        while (true) {
            try {
                $response = $next($request);

                // If response is a retryable error status (e.g. 429 or 5xx) and we haven't exhausted attempts
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
                    $delayMs = $this->retryPolicy->getDelayMs($attempt);
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

        if ($this->sleeper !== null && is_callable($this->sleeper)) {
            ($this->sleeper)($milliseconds);
            return;
        }

        usleep($milliseconds * 1000);
    }
}
