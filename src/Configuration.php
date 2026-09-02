<?php

declare(strict_types=1);

namespace Clusterify;

use Clusterify\Auth\Credentials;
use Clusterify\Support\RetryPolicy;
use Psr\Log\LoggerInterface;

/**
 * Immutable configuration settings for the Clusterify Client.
 */
final readonly class Configuration
{
    public const DEFAULT_BASE_URL = 'https://api.clusterify.ai';
    public const SDK_VERSION = '1.0.0';

    /**
     * @param Credentials $credentials Authentication credentials
     * @param string $baseUrl Base URL for the Clusterify API
     * @param int $timeoutMs Request timeout in milliseconds
     * @param int $connectTimeoutMs Connection timeout in milliseconds
     * @param RetryPolicy $retryPolicy Policy for automatic retries
     * @param LoggerInterface|null $logger Optional PSR-3 logger
     * @param array<string, string> $defaultHeaders Custom default headers for all requests
     * @param string $userAgent User Agent header string
     */
    public function __construct(
        public Credentials $credentials,
        public string $baseUrl = self::DEFAULT_BASE_URL,
        public int $timeoutMs = 30000,
        public int $connectTimeoutMs = 10000,
        public RetryPolicy $retryPolicy = new RetryPolicy(),
        public ?LoggerInterface $logger = null,
        public array $defaultHeaders = [],
        public string $userAgent = 'clusterify-chatbot-sdk/' . self::SDK_VERSION
    ) {}

    /**
     * Returns sanitized base URL without trailing slashes.
     */
    public function getCleanBaseUrl(): string
    {
        return rtrim($this->baseUrl, '/');
    }
}
