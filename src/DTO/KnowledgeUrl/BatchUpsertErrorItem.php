<?php

declare(strict_types=1);

namespace Clusterify\DTO\KnowledgeUrl;

/**
 * Error entry for a failed item in a batch upsert operation.
 */
final readonly class BatchUpsertErrorItem
{
    /**
     * @param int $index Zero-based array index of the failed item
     * @param string $url URL of the failed item
     * @param string $error Human-readable error reason
     */
    public function __construct(
        public int $index,
        public string $url,
        public string $error
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            index: (int) ($data['index'] ?? 0),
            url: (string) ($data['url'] ?? ''),
            error: (string) ($data['error'] ?? '')
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'index' => $this->index,
            'url' => $this->url,
            'error' => $this->error,
        ];
    }
}
