<?php

declare(strict_types=1);

namespace Clusterify\DTO\KnowledgeUrl;

use Clusterify\Support\Payload;
use JsonSerializable;

/**
 * Error entry for a failed item in a batch upsert operation.
 */
final readonly class BatchUpsertErrorItem implements JsonSerializable
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
            index: Payload::int($data, 'index', 0),
            url: Payload::string($data, 'url', ''),
            error: Payload::string($data, 'error', '')
        );
    }

    /**
     * @return array{index: int, url: string, error: string}
     */
    public function toArray(): array
    {
        return [
            'index' => $this->index,
            'url' => $this->url,
            'error' => $this->error,
        ];
    }

    /**
     * @return array{index: int, url: string, error: string}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
