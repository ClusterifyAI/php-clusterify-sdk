<?php

declare(strict_types=1);

namespace Clusterify\DTO\KnowledgeUrl;

/**
 * Result item for a successfully processed URL knowledge entry in a batch operation.
 */
final readonly class BatchUpsertResultItem
{
    /**
     * @param string $action Action performed ('created' or 'updated')
     * @param KnowledgeUrlItem $item Upserted URL knowledge item
     */
    public function __construct(
        public string $action,
        public KnowledgeUrlItem $item
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed> $itemNode */
        $itemNode = $data['item'] ?? [];

        return new self(
            action: (string) ($data['action'] ?? 'created'),
            item: KnowledgeUrlItem::fromArray($itemNode)
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'action' => $this->action,
            'item' => $this->item->toArray(),
        ];
    }
}
