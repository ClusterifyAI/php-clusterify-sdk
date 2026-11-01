<?php

declare(strict_types=1);

namespace Clusterify\DTO\Ping;

use Clusterify\Support\Payload;
use JsonSerializable;

/**
 * Healthcheck and ping response DTO.
 */
final readonly class PingResponse implements JsonSerializable
{
    /**
     * @param bool $success Whether the request succeeded
     * @param string $status Service health status (e.g. 'healthy')
     * @param bool $authenticated Whether the provided API keys are valid
     * @param string $timestamp Server ISO-8601 timestamp
     */
    public function __construct(
        public bool $success,
        public string $status,
        public bool $authenticated,
        public string $timestamp
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            success: Payload::bool($data, 'success', true),
            status: Payload::string($data, 'status', 'unknown'),
            authenticated: Payload::bool($data, 'authenticated', false),
            timestamp: Payload::string($data, 'timestamp', '')
        );
    }

    /**
     * @return array{success: bool, status: string, authenticated: bool, timestamp: string}
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'status' => $this->status,
            'authenticated' => $this->authenticated,
            'timestamp' => $this->timestamp,
        ];
    }

    /**
     * @return array{success: bool, status: string, authenticated: bool, timestamp: string}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
