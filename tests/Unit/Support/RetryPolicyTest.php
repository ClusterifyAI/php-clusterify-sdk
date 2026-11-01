<?php

declare(strict_types=1);

namespace Clusterify\Tests\Unit\Support;

use Clusterify\Exceptions\ClusterifyException;
use Clusterify\Exceptions\NetworkException;
use Clusterify\Support\RetryPolicy;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class RetryPolicyTest extends TestCase
{
    public function testParseRetryAfterSeconds(): void
    {
        $response = new Response(429, ['Retry-After' => '45']);
        $seconds = RetryPolicy::parseRetryAfter($response);
        $this->assertSame(45, $seconds);
    }

    public function testParseRetryAfterHttpDate(): void
    {
        $futureTime = time() + 100;
        $dateStr = gmdate('D, d M Y H:i:s T', $futureTime);
        $response = new Response(429, ['Retry-After' => $dateStr]);

        $seconds = RetryPolicy::parseRetryAfter($response);
        $this->assertNotNull($seconds);
        $this->assertGreaterThanOrEqual(95, $seconds);
        $this->assertLessThanOrEqual(105, $seconds);
    }

    public function testParseRetryAfterMissing(): void
    {
        $response = new Response(200);
        $this->assertNull(RetryPolicy::parseRetryAfter($response));
    }

    public function testShouldRetryStatusCodes(): void
    {
        $policy = new RetryPolicy(maxRetries: 3);

        $this->assertTrue($policy->shouldRetry(1, 429));
        $this->assertTrue($policy->shouldRetry(2, 500));
        $this->assertTrue($policy->shouldRetry(3, 503));
        $this->assertFalse($policy->shouldRetry(4, 500)); // Exceeded maxRetries
        $this->assertFalse($policy->shouldRetry(1, 400)); // 400 Bad Request is not retryable
        $this->assertFalse($policy->shouldRetry(1, 403)); // 403 Forbidden is not retryable
    }

    public function testShouldRetryOnNetworkExceptionOnly(): void
    {
        $policy = new RetryPolicy(maxRetries: 3);

        $this->assertTrue($policy->shouldRetry(1, null, new NetworkException('Connection timed out')));
        $this->assertFalse($policy->shouldRetry(4, null, new NetworkException('Connection timed out')));

        // Non-network local exceptions (such as JSON encode errors or runtime bugs) must NOT be retried
        $this->assertFalse($policy->shouldRetry(1, null, new RuntimeException('Unexpected local bug')));
        $this->assertFalse($policy->shouldRetry(1, null, new ClusterifyException('Bad UTF-8', 'JSON_ENCODE_ERROR')));
    }

    public function testGetDelayMsWithExponentialBackoff(): void
    {
        $policy = new RetryPolicy(baseDelayMs: 100, maxDelayMs: 5000, jitter: 0.0);

        $this->assertSame(100, $policy->getDelayMs(1));
        $this->assertSame(200, $policy->getDelayMs(2));
        $this->assertSame(400, $policy->getDelayMs(3));
    }

    public function testGetDelayMsWithRetryAfterHeaderAndSecondsOverride(): void
    {
        $policy = new RetryPolicy(baseDelayMs: 100, maxDelayMs: 60000, jitter: 0.0);
        $response = new Response(429, ['Retry-After' => '10']);

        $this->assertSame(10000, $policy->getDelayMs(1, $response));
        $this->assertSame(15000, $policy->getDelayMs(1, null, 15));
    }

    public function testJitterRespectsMaxDelayCapWithoutOverflow(): void
    {
        $policy = new RetryPolicy(baseDelayMs: 1000, maxDelayMs: 2000, jitter: 0.25);

        for ($i = 0; $i < 20; $i++) {
            $delay = $policy->getDelayMs(50);
            $this->assertGreaterThanOrEqual(1500, $delay);
            $this->assertLessThanOrEqual(2000, $delay);
        }
    }

    public function testInvalidConstructorBoundsThrow(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new RetryPolicy(maxRetries: 3, jitter: 1.5);
    }
}
