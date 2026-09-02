<?php

declare(strict_types=1);

namespace Clusterify\DTO\KnowledgeUrl;

use InvalidArgumentException;

/**
 * Payload for batch upserting multiple URL knowledge entries.
 */
final readonly class BatchUpsertUrlRequest
{
    /**
     * @param list<UpsertUrlRequest> $items
     */
    public function __construct(
        public array $items
    ) {
        if (empty($this->items)) {
            throw new InvalidArgumentException('BatchUpsertUrlRequest items array cannot be empty.');
        }
    }

    /**
     * Factory from an array of UpsertUrlRequest objects or associative arrays.
     *
     * @param list<UpsertUrlRequest|array<string, mixed>> $items
     */
    public static function fromList(array $items): self
    {
        $normalized = [];

        foreach ($items as $item) {
            if ($item instanceof UpsertUrlRequest) {
                $normalized[] = $item;
            } elseif (is_array($item)) {
                $normalized[] = new UpsertUrlRequest(
                    url: (string) ($item['url'] ?? ''),
                    content: (string) ($item['content'] ?? ''),
                    isEnabled: (bool) ($item['is_enabled'] ?? true)
                );
            }
        }

        return new self($normalized);
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public function toArray(): array
    {
        return [
            'items' => array_map(static fn(UpsertUrlRequest $item): array => $item->toArray(), $this->items),
        ];
    }
}
