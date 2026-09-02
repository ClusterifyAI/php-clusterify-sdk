<?php

declare(strict_types=1);

namespace Clusterify\Tests\Unit\Http;

use Clusterify\Http\Middleware\LoggingMiddleware;
use Clusterify\Http\Request;
use Clusterify\Http\Response;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;

final class LoggingMiddlewareTest extends TestCase
{
    public function testSanitizesSensitiveHeaders(): void
    {
        $loggedContexts = [];

        $logger = new class($loggedContexts) extends AbstractLogger {
            /** @param array<int, array<string, mixed>> $loggedContexts */
            public function __construct(public array &$loggedContexts) {}

            public function log($level, \Stringable|string $message, array $context = []): void
            {
                $this->loggedContexts[] = ['message' => (string) $message, 'context' => $context];
            }
        };

        $middleware = new LoggingMiddleware($logger);

        $request = new Request(
            method: 'GET',
            path: '/v1/dashboard/profile',
            queryParams: [],
            body: null,
            headers: [
                'x-public-key' => 'pk_live_123',
                'x-secret-key' => 'sk_live_secret_value',
                'Authorization' => 'Bearer sk_live_secret_value',
            ]
        );

        $next = fn(Request $req): Response => new Response(200, ['success' => true]);

        $response = $middleware->handle($request, $next);
        $this->assertTrue($response->isSuccess());

        $this->assertNotEmpty($loggedContexts);
        $requestLogContext = $loggedContexts[0]['context'];
        $headers = $requestLogContext['headers'];

        $this->assertSame('pk_live_123', $headers['x-public-key']);
        $this->assertSame('[REDACTED]', $headers['x-secret-key']);
        $this->assertSame('[REDACTED]', $headers['Authorization']);
    }
}
