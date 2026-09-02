<?php

declare(strict_types=1);

namespace Clusterify\Tests\Integration;

use Clusterify\ClusterifyClient;
use Clusterify\Exceptions\AuthenticationException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use PHPUnit\Framework\TestCase;

final class ClientIntegrationTest extends TestCase
{
    public function testClientBuilderAndPing(): void
    {
        $mockHandler = new MockHandler([
            new GuzzleResponse(200, [], '{"success":true,"status":"healthy","authenticated":true,"timestamp":"2026-08-31T21:15:00.000Z"}'),
        ]);
        $handlerStack = HandlerStack::create($mockHandler);
        $guzzle = new GuzzleClient(['handler' => $handlerStack]);

        $client = ClusterifyClient::builder()
            ->withCredentials('pk_live_test123', 'sk_live_test456')
            ->withHttpClient($guzzle)
            ->build();

        $ping = $client->ping();
        $this->assertTrue($ping->success);
        $this->assertSame('healthy', $ping->status);
        $this->assertTrue($ping->authenticated);
    }

    public function testAuthenticationErrorHandling(): void
    {
        $mockHandler = new MockHandler([
            new GuzzleResponse(401, [], '{"success":false,"error":"INVALID_API_KEYS","message":"Invalid API keys"}'),
        ]);
        $handlerStack = HandlerStack::create($mockHandler);
        $guzzle = new GuzzleClient(['handler' => $handlerStack]);

        $client = ClusterifyClient::builder()
            ->withCredentials('pk_bad', 'sk_bad')
            ->withHttpClient($guzzle)
            ->withoutRetries()
            ->build();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid API keys');
        $client->profile()->get();
    }
}
