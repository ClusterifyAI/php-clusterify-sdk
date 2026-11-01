<?php

declare(strict_types=1);

namespace Clusterify\Http\Middleware;

use Clusterify\Http\Request;
use Clusterify\Http\Response;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * PSR-3 Logging middleware that logs HTTP requests, latencies, and responses while redacting secret credentials.
 */
final readonly class LoggingMiddleware implements MiddlewareInterface
{
    private const SENSITIVE_HEADERS = [
        'x-secret-key',
        'x-api-secret',
        'authorization',
        'proxy-authorization',
    ];

    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function handle(Request $request, callable $next): Response
    {
        $startTime = microtime(true);
        $combinedHeaders = array_merge($request->headers, $request->options?->headers ?? []);
        $context = [
            'method' => $request->method,
            'path' => $request->path,
            'query' => $request->queryParams,
            'headers' => $this->sanitizeHeaders($combinedHeaders),
        ];

        $this->logger->debug("Clusterify API Request: {$request->method} {$request->path}", $context);

        try {
            $response = $next($request);
            $durationMs = round((microtime(true) - $startTime) * 1000, 2);

            $this->logger->debug("Clusterify API Response: {$request->method} {$request->path} -> {$response->statusCode} ({$durationMs}ms)", [
                'status_code' => $response->statusCode,
                'duration_ms' => $durationMs,
                'success' => $response->isSuccess(),
            ]);

            return $response;
        } catch (Throwable $e) {
            $durationMs = round((microtime(true) - $startTime) * 1000, 2);

            $this->logger->error("Clusterify API Request Failed: {$request->method} {$request->path} ({$durationMs}ms) - {$e->getMessage()}", [
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'duration_ms' => $durationMs,
            ]);

            throw $e;
        }
    }

    /**
     * Redact sensitive authentication headers.
     *
     * @param array<string, string> $headers
     * @return array<string, string>
     */
    private function sanitizeHeaders(array $headers): array
    {
        $sanitized = [];
        foreach ($headers as $key => $value) {
            if (in_array(strtolower($key), self::SENSITIVE_HEADERS, true)) {
                $sanitized[$key] = '[REDACTED]';
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
}
