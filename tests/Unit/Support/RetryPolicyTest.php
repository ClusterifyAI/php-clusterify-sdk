<?php

declare(strict_types=1);

namespace Clusterify\Tests\Unit\Support;

use Clusterify\Support\RetryPolicy;
use GuzzleHttp\Psr7\Response;
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

    public function testShouldRetryOnNetworkException(): void
    {
        $policy = new RetryPolicy(maxRetries: 3);
        $this->assertTrue($policy->shouldRetry(1, null, new RuntimeException('Connection timed out')));
        $this->assertFalse($policy->shouldRetry(4, null, new RuntimeException('Connection timed out')));
    }

    public function testGetDelayMsWithExponentialBackoff(): void
    {
        $policy = new RetryPolicy(baseDelayMs: 100, maxDelayMs: 5000, jitter: 0.0);

        $this->assertSame(100, $policy->getDelayMs(1));
        $this->assertSame(200, $policy->getDelayMs(2));
        $this->assertSame(400, $policy->getDelayMs(3));
    }

    public function testGetDelayMsWithRetryAfterHeader(): void
    {
        $policy = new RetryPolicy(baseDelayMs: 100, maxDelayMs: 60000, jitter: 0.0);
        $response = new Response(429, ['Retry-After' => '10']);

        $delay = $policy->getDelayMs(1, $response);
        $this->assertSame(10000, $delay);
    }
}
