<?php

declare(strict_types=1);

namespace Clusterify;

use Clusterify\Auth\AuthMethod;
use Clusterify\Auth\Credentials;
use Clusterify\Http\Middleware\LoggingMiddleware;
use Clusterify\Http\Middleware\MiddlewareInterface;
use Clusterify\Http\Middleware\RetryMiddleware;
use Clusterify\Http\PsrHttpClient;
use Clusterify\Support\RetryPolicy;
use InvalidArgumentException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use SensitiveParameter;

/**
 * Fluent builder for configuring and instantiating a ClusterifyClient.
 */
final class ClusterifyClientBuilder
{
    private ?Credentials $credentials = null;
    private string $baseUrl = Configuration::DEFAULT_BASE_URL;
    private int $timeoutMs = 30000;
    private int $connectTimeoutMs = 10000;
    private ?RetryPolicy $retryPolicy = null;
    private ?LoggerInterface $logger = null;
    /** @var array<string, string> */
    private array $defaultHeaders = [];
    private string $userAgent = 'clusterify-chatbot-sdk/' . Configuration::SDK_VERSION;

    private ?ClientInterface $psrClient = null;
    private ?RequestFactoryInterface $requestFactory = null;
    private ?StreamFactoryInterface $streamFactory = null;

    /** @var list<MiddlewareInterface> */
    private array $customMiddlewares = [];

    /**
     * Set API authentication credentials.
     */
    public function withCredentials(
        string $publicKey,
        #[SensitiveParameter]
        string $secretKey,
        AuthMethod $authMethod = AuthMethod::CustomHeaders
    ): self {
        $this->credentials = new Credentials($publicKey, $secretKey, $authMethod);
        return $this;
    }

    /**
     * Set custom API Base URL.
     *
     * @throws InvalidArgumentException
     */
    public function withBaseUrl(string $baseUrl): self
    {
        if (trim($baseUrl) === '') {
            throw new InvalidArgumentException('Base URL must not be empty.');
        }

        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * Set request timeout in milliseconds.
     *
     * @throws InvalidArgumentException
     */
    public function withTimeout(int $timeoutMs): self
    {
        if ($timeoutMs <= 0) {
            throw new InvalidArgumentException('Request timeout (timeoutMs) must be greater than 0.');
        }

        $this->timeoutMs = $timeoutMs;
        return $this;
    }

    /**
     * Set connection timeout in milliseconds.
     *
     * @throws InvalidArgumentException
     */
    public function withConnectTimeout(int $connectTimeoutMs): self
    {
        if ($connectTimeoutMs <= 0) {
            throw new InvalidArgumentException('Connection timeout (connectTimeoutMs) must be greater than 0.');
        }

        $this->connectTimeoutMs = $connectTimeoutMs;
        return $this;
    }

    /**
     * Set custom retry policy for exponential backoff and jitter.
     */
    public function withRetryPolicy(RetryPolicy $retryPolicy): self
    {
        $this->retryPolicy = $retryPolicy;
        return $this;
    }

    /**
     * Disable automatic retries.
     */
    public function withoutRetries(): self
    {
        $this->retryPolicy = new RetryPolicy(maxRetries: 0);
        return $this;
    }

    /**
     * Set PSR-3 logger for audit logging, latency tracking, and debug output.
     */
    public function withLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Add a default HTTP header.
     */
    public function withHeader(string $name, string $value): self
    {
        $this->defaultHeaders[$name] = $value;
        return $this;
    }

    /**
     * Set default HTTP headers.
     *
     * @param array<string, string> $headers
     */
    public function withHeaders(array $headers): self
    {
        $this->defaultHeaders = array_merge($this->defaultHeaders, $headers);
        return $this;
    }

    /**
     * Set custom User-Agent string.
     */
    public function withUserAgent(string $userAgent): self
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    /**
     * Set custom PSR-18 HTTP Client.
     */
    public function withHttpClient(ClientInterface $psrClient): self
    {
        $this->psrClient = $psrClient;
        return $this;
    }

    /**
     * Set custom PSR-17 Request Factory.
     */
    public function withRequestFactory(RequestFactoryInterface $requestFactory): self
    {
        $this->requestFactory = $requestFactory;
        return $this;
    }

    /**
     * Set custom PSR-17 Stream Factory.
     */
    public function withStreamFactory(StreamFactoryInterface $streamFactory): self
    {
        $this->streamFactory = $streamFactory;
        return $this;
    }

    /**
     * Add a custom middleware to the transport pipeline.
     */
    public function withMiddleware(MiddlewareInterface $middleware): self
    {
        $this->customMiddlewares[] = $middleware;
        return $this;
    }

    /**
     * Build and return the configured ClusterifyClient instance.
     *
     * @throws InvalidArgumentException If credentials are missing
     */
    public function build(): ClusterifyClient
    {
        if ($this->credentials === null) {
            throw new InvalidArgumentException('Clusterify credentials (publicKey & secretKey) must be provided.');
        }

        $retryPolicy = $this->retryPolicy ?? new RetryPolicy();

        $configuration = new Configuration(
            credentials: $this->credentials,
            baseUrl: $this->baseUrl,
            timeoutMs: $this->timeoutMs,
            connectTimeoutMs: $this->connectTimeoutMs,
            retryPolicy: $retryPolicy,
            logger: $this->logger,
            defaultHeaders: $this->defaultHeaders,
            userAgent: $this->userAgent
        );

        $middlewares = [];

        // Attach logging middleware if logger provided
        if ($this->logger !== null) {
            $middlewares[] = new LoggingMiddleware($this->logger);
        }

        // Attach retry middleware if retries enabled
        if ($retryPolicy->maxRetries > 0) {
            $middlewares[] = new RetryMiddleware($retryPolicy, $this->logger);
        }

        // Attach custom middlewares
        foreach ($this->customMiddlewares as $customMiddleware) {
            $middlewares[] = $customMiddleware;
        }

        $httpClient = new PsrHttpClient(
            configuration: $configuration,
            psrClient: $this->psrClient,
            requestFactory: $this->requestFactory,
            streamFactory: $this->streamFactory,
            middlewares: $middlewares
        );

        return new ClusterifyClient($configuration, $httpClient);
    }
}
