<?php

declare(strict_types=1);

namespace Clusterify\DTO\KnowledgeUrl;

use Clusterify\Support\Payload;
use JsonSerializable;

/**
 * Result item for a successfully processed URL knowledge entry in a batch operation.
 */
final readonly class BatchUpsertResultItem implements JsonSerializable
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
        $itemNode = Payload::map($data, 'item');

        return new self(
            action: Payload::string($data, 'action', 'created'),
            item: KnowledgeUrlItem::fromArray($itemNode)
        );
    }

    /**
     * @return array{action: string, item: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'action' => $this->action,
            'item' => $this->item->toArray(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
