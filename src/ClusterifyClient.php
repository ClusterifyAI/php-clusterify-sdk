<?php

declare(strict_types=1);

namespace Clusterify;

use Clusterify\Auth\AuthMethod;
use Clusterify\Auth\Credentials;
use Clusterify\DTO\Ping\PingResponse;
use Clusterify\Http\HttpClientInterface;
use Clusterify\Http\PsrHttpClient;
use Clusterify\Http\RequestOptions;
use Clusterify\Resources\ChatbotResource;
use Clusterify\Resources\InstallationResource;
use Clusterify\Resources\KnowledgeResource;
use Clusterify\Resources\KnowledgeUrlResource;
use Clusterify\Resources\PingResource;
use Clusterify\Resources\ProfileResource;

/**
 * Primary client entrypoint for Clusterify.AI Chatbot API.
 */
class ClusterifyClient
{
    private ?PingResource $pingResource = null;
    private ?ProfileResource $profileResource = null;
    private ?ChatbotResource $chatbotResource = null;
    private ?InstallationResource $installationResource = null;
    private ?KnowledgeResource $knowledgeResource = null;
    private ?KnowledgeUrlResource $knowledgeUrlResource = null;

    /**
     * @param Configuration $configuration Client configuration
     * @param HttpClientInterface $httpClient Transport HTTP client
     */
    public function __construct(
        private readonly Configuration $configuration,
        private readonly HttpClientInterface $httpClient
    ) {}

    /**
     * Zero-config factory to quickly instantiate an authenticated Clusterify client.
     *
     * @param string $publicKey Tenant public key (e.g. 'pk_live_...')
     * @param string $secretKey Tenant secret key (e.g. 'sk_live_...')
     * @param string|null $baseUrl Optional API base URL override
     * @param AuthMethod $authMethod Authentication method (default: CustomHeaders)
     * @return self
     */
    public static function create(
        string $publicKey,
        string $secretKey,
        ?string $baseUrl = null,
        AuthMethod $authMethod = AuthMethod::CustomHeaders
    ): self {
        $builder = self::builder()
            ->withCredentials($publicKey, $secretKey, $authMethod);

        if ($baseUrl !== null && trim($baseUrl) !== '') {
            $builder->withBaseUrl($baseUrl);
        }

        return $builder->build();
    }

    /**
     * Start a fluent client builder for advanced configuration (custom PSR-18 clients, logging, retries).
     *
     * @return ClusterifyClientBuilder
     */
    public static function builder(): ClusterifyClientBuilder
    {
        return new ClusterifyClientBuilder();
    }

    /**
     * Perform a fast healthcheck & authentication ping.
     *
     * @param RequestOptions|null $options
     * @return PingResponse
     */
    public function ping(?RequestOptions $options = null): PingResponse
    {
        return $this->pingResource()->ping($options);
    }

    /**
     * Access the Ping resource.
     */
    public function pingResource(): PingResource
    {
        return $this->pingResource ??= new PingResource($this->httpClient);
    }

    /**
     * Access the Profile & Subscription Plan resource.
     */
    public function profile(): ProfileResource
    {
        return $this->profileResource ??= new ProfileResource($this->httpClient);
    }

    /**
     * Access the Chatbot Configuration resource.
     */
    public function chatbot(): ChatbotResource
    {
        return $this->chatbotResource ??= new ChatbotResource($this->httpClient);
    }

    /**
     * Access the Installation & HTML Snippet resource.
     */
    public function installation(): InstallationResource
    {
        return $this->installationResource ??= new InstallationResource($this->httpClient);
    }

    /**
     * Access the Simple Website Knowledge Base resource (knowledge_builder).
     */
    public function knowledge(): KnowledgeResource
    {
        return $this->knowledgeResource ??= new KnowledgeResource($this->httpClient);
    }

    /**
     * Alias for knowledge() resource.
     */
    public function knowledges(): KnowledgeResource
    {
        return $this->knowledge();
    }

    /**
     * Access the URL-Based Knowledge Base resource (knowledge_url - Professional Plan).
     */
    public function knowledgeUrl(): KnowledgeUrlResource
    {
        return $this->knowledgeUrlResource ??= new KnowledgeUrlResource($this->httpClient);
    }

    /**
     * Alias for knowledgeUrl() resource.
     */
    public function knowledgeUrls(): KnowledgeUrlResource
    {
        return $this->knowledgeUrl();
    }

    /**
     * Access the underlying HTTP client transport.
     */
    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }

    /**
     * Access the immutable client configuration.
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }
}
