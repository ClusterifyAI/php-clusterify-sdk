<?php

declare(strict_types=1);

namespace Clusterify\Tests\Unit\Auth;

use Clusterify\Auth\AuthMethod;
use Clusterify\Auth\Credentials;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CredentialsTest extends TestCase
{
    public function testConstructWithValidKeys(): void
    {
        $creds = new Credentials('pk_live_12345678', 'sk_live_87654321');
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
