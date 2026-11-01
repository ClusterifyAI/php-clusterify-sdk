<?php

declare(strict_types=1);

namespace Clusterify\Tests\Unit\Auth;

use Clusterify\Auth\AuthMethod;
use Clusterify\Auth\Credentials;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use SensitiveParameter;

final class CredentialsTest extends TestCase
{
    public function testConstructWithValidKeysAndTrimsOuterWhitespace(): void
    {
        $creds = new Credentials("  pk_live_12345678  \n", "  sk_live_87654321\r\n");
        $this->assertSame('pk_live_12345678', $creds->publicKey);
        $this->assertSame('sk_live_87654321', $creds->secretKey);
        $this->assertSame(AuthMethod::CustomHeaders, $creds->authMethod);
    }

    public function testConstructThrowsOnEmptyPublicKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Clusterify public key must not be empty.');
        new Credentials('   ', 'sk_live_123');
    }

    public function testConstructThrowsOnEmptySecretKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Clusterify secret key must not be empty.');
        new Credentials('pk_live_123', '');
    }

    public function testConstructThrowsOnHeaderInjectionControlCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('contains invalid control characters');
        new Credentials('pk_live_123', "sk_live_123\r\nEvil-Header: injected");
    }

    public function testSecretKeyHasSensitiveParameterAttribute(): void
    {
        $ref = new ReflectionMethod(Credentials::class, '__construct');
        $secretParam = $ref->getParameters()[1];
        $attrs = $secretParam->getAttributes(SensitiveParameter::class);

        $this->assertCount(1, $attrs);
    }

    public function testToHeadersCustomHeaders(): void
    {
        $creds = new Credentials('pk_test', 'sk_test', AuthMethod::CustomHeaders);
        $headers = $creds->toHeaders();

        $this->assertSame([
            'x-public-key' => 'pk_test',
            'x-secret-key' => 'sk_test',
        ], $headers);
    }

    public function testToHeadersBearer(): void
    {
        $creds = new Credentials('pk_test', 'sk_test', AuthMethod::Bearer);
        $headers = $creds->toHeaders();

        $this->assertSame([
            'x-public-key' => 'pk_test',
            'Authorization' => 'Bearer sk_test',
        ], $headers);
    }

    public function testToHeadersBasic(): void
    {
        $creds = new Credentials('pk_test', 'sk_test', AuthMethod::Basic);
        $headers = $creds->toHeaders();

        $this->assertSame([
            'Authorization' => 'Basic ' . base64_encode('pk_test:sk_test'),
        ], $headers);
    }

    public function testDebugInfoMasksSecretKey(): void
    {
        $creds = new Credentials('pk_live_abcdef123456', 'sk_live_secret1234567890abcdef');
        $debug = $creds->__debugInfo();

        $this->assertSame('pk_live_abcdef123456', $debug['publicKey']);
        $this->assertNotSame('sk_live_secret1234567890abcdef', $debug['secretKey']);
        $this->assertStringStartsWith('sk_live', $debug['secretKey']);
        $this->assertStringContainsString('...', $debug['secretKey']);
        $this->assertStringEndsWith('cdef', $debug['secretKey']);
    }
}
