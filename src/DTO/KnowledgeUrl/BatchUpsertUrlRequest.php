<?php

declare(strict_types=1);

namespace Clusterify\DTO\KnowledgeUrl;

use Clusterify\Support\Payload;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Payload for batch upserting multiple URL knowledge entries.
 */
final readonly class BatchUpsertUrlRequest implements JsonSerializable
{
    /**
     * @param list<UpsertUrlRequest> $items
     */
    public function __construct(
        public array $items
    ) {
        if ($this->items === []) {
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
                    url: Payload::string($item, 'url', ''),
                    content: Payload::string($item, 'content', ''),
                    isEnabled: Payload::bool($item, 'is_enabled', true),
                    id: Payload::nullableInt($item, 'id')
                );
            }
        }

        return new self($normalized);
    }

    /**
     * @return array{items: list<array<string, int|string|bool>>}
     */
    public function toArray(): array
    {
        return [
            'items' => array_map(static fn(UpsertUrlRequest $item): array => $item->toArray(), $this->items),
        ];
    }

    /**
     * @return array{items: list<array<string, int|string|bool>>}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
