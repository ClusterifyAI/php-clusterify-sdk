<?php

declare(strict_types=1);

namespace Clusterify\Tests\Unit\Helpers;

use Clusterify\Helpers\UrlNormalizer;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class UrlNormalizerTest extends TestCase
{
    public function testNormalizesRelativePath(): void
    {
        $url = UrlNormalizer::normalize('/pricing', 'https://example.com');
        $this->assertSame('https://example.com/pricing', $url);

        $url2 = UrlNormalizer::normalize('pricing', 'https://example.com/');
        $this->assertSame('https://example.com/pricing', $url2);
    }

    public function testNormalizesMatchingFullUrl(): void
    {
        $url = UrlNormalizer::normalize('https://example.com/docs/intro', 'https://example.com');
        $this->assertSame('https://example.com/docs/intro', $url);
    }

    public function testThrowsOnDomainMismatch(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("URL host 'otherdomain.com' does not match");
        UrlNormalizer::normalize('https://otherdomain.com/docs', 'https://example.com');
    }
}
