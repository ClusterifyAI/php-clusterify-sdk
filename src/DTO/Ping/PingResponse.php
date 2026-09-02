<?php

declare(strict_types=1);

namespace Clusterify\DTO\Ping;

/**
 * Healthcheck and ping response DTO.
 */
final readonly class PingResponse
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
            success: (bool) ($data['success'] ?? true),
            status: (string) ($data['status'] ?? 'unknown'),
            authenticated: (bool) ($data['authenticated'] ?? false),
            timestamp: (string) ($data['timestamp'] ?? '')
        );
    }

    /**
     * @return array<string, mixed>
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
}
