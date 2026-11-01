<?php

declare(strict_types=1);

namespace Clusterify\Tests\Unit\Http;

use Clusterify\Auth\Credentials;
use Clusterify\Configuration;
use Clusterify\Exceptions\ClusterifyException;
use Clusterify\Exceptions\ServerException;
use Clusterify\Http\Middleware\LoggingMiddleware;
use Clusterify\Http\PsrHttpClient;
use Clusterify\Http\RequestOptions;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware as GuzzleMiddleware;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Log\AbstractLogger;

final class PsrHttpClientTest extends TestCase
{
    public function testHtmlGateway502ThrowsServerExceptionInsteadOfJsonDecodeError(): void
    {
        $mock = new MockHandler([
            new GuzzleResponse(502, ['Content-Type' => 'text/html'], '<html><body><h1>502 Bad Gateway</h1></body></html>'),
        ]);
        $guzzle = new GuzzleClient(['handler' => HandlerStack::create($mock)]);
        $config = new Configuration(new Credentials('pk_live_123', 'sk_live_456'));
        $http = new PsrHttpClient($config, $guzzle);

        try {
            $http->get('/v1/dashboard/ping');
            $this->fail('Expected ServerException was not thrown.');
        } catch (ServerException $e) {
            $this->assertSame(502, $e->getStatusCode());
            $this->assertSame('SERVER_ERROR', $e->getErrorCode());
        }
    }

    public function testScalarJson200ThrowsJsonDecodeError(): void
    {
        $mock = new MockHandler([
            new GuzzleResponse(200, ['Content-Type' => 'application/json'], '"just-a-string"'),
        ]);
        $guzzle = new GuzzleClient(['handler' => HandlerStack::create($mock)]);
        $config = new Configuration(new Credentials('pk_live_123', 'sk_live_456'));
        $http = new PsrHttpClient($config, $guzzle);

        $this->expectException(ClusterifyException::class);
        $this->expectExceptionMessage('expected JSON object or array');
        $http->get('/v1/dashboard/ping');
    }

    public function testNoCacheAndAuthRedactionInFullPipeline(): void
    {
        /** @var array<int, array{request: RequestInterface, options: array<string, mixed>}> $history */
        $history = [];
        $mock = new MockHandler([
            new GuzzleResponse(200, [], '{"success":true,"status":"healthy","authenticated":true,"timestamp":"2026-11-01T00:00:00Z"}'),
        ]);
        $stack = HandlerStack::create($mock);
        $stack->push(GuzzleMiddleware::history($history));
        $guzzle = new GuzzleClient(['handler' => $stack]);

        $logs = [];
        $logger = new class($logs) extends AbstractLogger {
            /** @param array<int, array{message: string, context: array<string, mixed>}> $logs */
            public function __construct(public array &$logs) {}

            public function log($level, \Stringable|string $message, array $context = []): void
            {
                $this->logs[] = ['message' => (string) $message, 'context' => $context];
            }
        };

        $config = new Configuration(new Credentials('pk_live_abc', 'sk_live_supersecret9999'));
        $http = new PsrHttpClient($config, $guzzle, middlewares: [new LoggingMiddleware($logger)]);

        $options = new RequestOptions(noCache: true, timeoutMs: 4500, headers: ['X-Custom' => 'yes']);
        $res = $http->get('/v1/dashboard/ping', [], $options);

        $this->assertTrue($res->isSuccess());
        $this->assertCount(1, $history);

        $sentRequest = $history[0]['request'];
        $sentOptions = $history[0]['options'];

        $this->assertSame('nocache=1', $sentRequest->getUri()->getQuery());
        $this->assertFalse($sentRequest->hasHeader('x-no-cache'));
        $this->assertSame('pk_live_abc', $sentRequest->getHeaderLine('x-public-key'));
        $this->assertSame('sk_live_supersecret9999', $sentRequest->getHeaderLine('x-secret-key'));
        $this->assertSame(4.5, $sentOptions['timeout']);

        /** @var array<string, string> $loggedHeaders */
        $loggedHeaders = $logs[0]['context']['headers'];
        $this->assertSame('pk_live_abc', $loggedHeaders['x-public-key']);
        $this->assertSame('[REDACTED]', $loggedHeaders['x-secret-key']);
        $this->assertSame('yes', $loggedHeaders['X-Custom']);
    }
}

