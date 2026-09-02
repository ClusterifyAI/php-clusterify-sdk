<?php

declare(strict_types=1);

namespace Clusterify\Auth;

use InvalidArgumentException;

/**
 * Immutable credentials container holding Clusterify API public and secret keys.
 * Implements __debugInfo to prevent accidental leakage in stack traces and logs.
 */
final readonly class Credentials
{
    /**
     * @param string $publicKey Tenant public key (e.g. 'pk_live_...')
     * @param string $secretKey Tenant secret key (e.g. 'sk_live_...')
     * @param AuthMethod $authMethod Authentication method to use in HTTP requests
     */
    public function __construct(
        public string $publicKey,
        public string $secretKey,
        public AuthMethod $authMethod = AuthMethod::CustomHeaders
    ) {
        $cleanPublic = trim($publicKey);
        $cleanSecret = trim($secretKey);

        if ($cleanPublic === '') {
            throw new InvalidArgumentException('Clusterify public key must not be empty.');
        }

        if ($cleanSecret === '') {
            throw new InvalidArgumentException('Clusterify secret key must not be empty.');
        }
    }

    /**
     * Generate authentication HTTP headers for an outgoing request based on configured AuthMethod.
     *
     * @return array<string, string>
     */
    public function toHeaders(): array
    {
        return match ($this->authMethod) {
            AuthMethod::CustomHeaders => [
                'x-public-key' => $this->publicKey,
                'x-secret-key' => $this->secretKey,
            ],
            AuthMethod::Bearer => [
                'x-public-key' => $this->publicKey,
                'Authorization' => 'Bearer ' . $this->secretKey,
            ],
            AuthMethod::Basic => [
                'Authorization' => 'Basic ' . base64_encode($this->publicKey . ':' . $this->secretKey),
            ],
        };
    }

    /**
     * Obfuscate secret key when dumping or inspecting the object.
     *
     * @return array<string, string>
     */
    public function __debugInfo(): array
    {
        $secretLen = strlen($this->secretKey);
        $maskedSecret = $secretLen > 8
            ? substr($this->secretKey, 0, 7) . '...' . substr($this->secretKey, -4)
            : '********';

        return [
            'publicKey' => $this->publicKey,
            'secretKey' => $maskedSecret,
            'authMethod' => $this->authMethod->value,
        ];
    }
}
