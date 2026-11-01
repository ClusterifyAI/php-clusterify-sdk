<?php

declare(strict_types=1);

namespace Clusterify\Http;

use Clusterify\Support\Payload;
use Psr\Http\Message\ResponseInterface;

/**
 * Value object wrapping incoming HTTP response with decoded JSON payload.
 */
final readonly class Response
{
    /**
     * @param int $statusCode HTTP status code
     * @param array<string, mixed> $payload Decoded JSON envelope
     * @param array<string, list<string>> $headers Response headers
     * @param ResponseInterface|null $psrResponse Original PSR-7 response
     */
    public function __construct(
        public int $statusCode,
        public array $payload,
        public array $headers = [],
        public ?ResponseInterface $psrResponse = null
    ) {}

    /**
     * Check if the API response indicates success.
     */
    public function isSuccess(): bool
    {
        return $this->statusCode >= 200
            && $this->statusCode < 300
            && Payload::bool($this->payload, 'success', true);
    }

    /**
     * Access response data payload if present.
     *
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return Payload::map($this->payload, 'data');
    }

    /**
     * Access top-level response message if present.
     */
    public function getMessage(): ?string
    {
        return Payload::nullableString($this->payload, 'message');
    }
}
