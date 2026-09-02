<?php

declare(strict_types=1);

namespace Clusterify\Http;

/**
 * Value object representing an outgoing HTTP request before PSR-7 construction.
 */
final readonly class Request
{
    /**
     * @param string $method HTTP method (GET, POST, PATCH, DELETE)
     * @param string $path URL path relative to API base (e.g. '/v1/dashboard/ping')
     * @param array<string, mixed> $queryParams Query parameters
     * @param array<string, mixed>|null $body JSON payload body
     * @param array<string, string> $headers Additional headers
     * @param RequestOptions|null $options Per-request options
     */
    public function __construct(
        public string $method,
        public string $path,
        public array $queryParams = [],
        public ?array $body = null,
        public array $headers = [],
        public ?RequestOptions $options = null
    ) {}

    /**
     * Return new Request instance with merged query parameters.
     *
     * @param array<string, mixed> $params
     */
    public function withQueryParams(array $params): self
    {
        return new self(
            method: $this->method,
            path: $this->path,
            queryParams: array_merge($this->queryParams, $params),
            body: $this->body,
            headers: $this->headers,
            options: $this->options
        );
    }
}
