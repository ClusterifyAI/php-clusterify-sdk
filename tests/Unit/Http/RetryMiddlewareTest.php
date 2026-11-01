<?php

declare(strict_types=1);

namespace Clusterify\Tests\Unit\Http;

use Clusterify\Auth\Credentials;
use Clusterify\Configuration;
use Clusterify\Exceptions\ValidationException;
use Clusterify\Http\Middleware\RetryMiddleware;
use Clusterify\Http\PsrHttpClient;
use Clusterify\Support\RetryPolicy;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use PHPUnit\Framework\TestCase;

final class RetryMiddlewareTest extends TestCase
{
    public function testHonorsRetryAfterHeaderOn429ExceptionFromPsrHttpClient(): void
    {
        $mock = new MockHandler([
            new GuzzleResponse(429, ['Retry-After' => '7'], '{"success":false,"error":"RATE_LIMIT_EXCEEDED","message":"Slow down"}'),
            new GuzzleResponse(200, [], '{"success":true,"status":"healthy","authenticated":true,"timestamp":"2026-11-01T00:00:00Z"}'),
        ]);
        $guzzle = new GuzzleClient(['handler' => HandlerStack::create($mock)]);

        $recordedSleeps = [];
        $policy = new RetryPolicy(maxRetries: 2, baseDelayMs: 250, maxDelayMs: 30000, jitter: 0.0);
        $retryMiddleware = new RetryMiddleware(
            retryPolicy: $policy,
            sleeper: static function (int $ms) use (&$recordedSleeps): void {
                $recordedSleeps[] = $ms;
            }
        );

        $config = new Configuration(new Credentials('pk_live_123', 'sk_live_456'), retryPolicy: $policy);
        $http = new PsrHttpClient($config, $guzzle, middlewares: [$retryMiddleware]);

        $response = $http->get('/v1/dashboard/ping');

        $this->assertSame(200, $response->statusCode);
        $this->assertSame([7000], $recordedSleeps);
    }

    public function testRetries502HtmlGatewayErrorAndSucceeds(): void
    {
        $mock = new MockHandler([
            new GuzzleResponse(502, [], '<html>502 Bad Gateway</html>'),
            new GuzzleResponse(200, [], '{"success":true}'),
        ]);
        $guzzle = new GuzzleClient(['handler' => HandlerStack::create($mock)]);

        $recordedSleeps = [];
        $policy = new RetryPolicy(maxRetries: 2, baseDelayMs: 300, maxDelayMs: 5000, jitter: 0.0);
        $retryMiddleware = new RetryMiddleware(
            retryPolicy: $policy,
            sleeper: static function (int $ms) use (&$recordedSleeps): void {
                $recordedSleeps[] = $ms;
            }
        );

        $config = new Configuration(new Credentials('pk_live_123', 'sk_live_456'), retryPolicy: $policy);
        $http = new PsrHttpClient($config, $guzzle, middlewares: [$retryMiddleware]);

        $response = $http->get('/v1/dashboard/ping');

        $this->assertTrue($response->isSuccess());
        $this->assertSame([300], $recordedSleeps);
    }

    public function testDoesNotRetry400ValidationException(): void
    {
        $mock = new MockHandler([
            new GuzzleResponse(400, [], '{"success":false,"error":"VALIDATION_ERROR","message":"Bad field"}'),
        ]);
        $guzzle = new GuzzleClient(['handler' => HandlerStack::create($mock)]);

        $recordedSleeps = [];
        $policy = new RetryPolicy(maxRetries: 3, baseDelayMs: 100, jitter: 0.0);
        $retryMiddleware = new RetryMiddleware(
            retryPolicy: $policy,
            sleeper: static function (int $ms) use (&$recordedSleeps): void {
                $recordedSleeps[] = $ms;
            }
        );

        $config = new Configuration(new Credentials('pk_live_123', 'sk_live_456'), retryPolicy: $policy);
        $http = new PsrHttpClient($config, $guzzle, middlewares: [$retryMiddleware]);

        try {
            $http->post('/v1/dashboard/knowledge', ['title' => '']);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException) {
            $this->assertSame([], $recordedSleeps);
        }
    }
}

