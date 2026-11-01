<?php

declare(strict_types=1);

namespace Clusterify\DTO\Knowledge;

use Clusterify\Support\Payload;
use JsonSerializable;

/**
 * Knowledge base listing response envelope containing sections array and plan usage.
 */
final readonly class KnowledgeListResponse implements JsonSerializable
{
    /**
     * @param bool $success Whether the request succeeded
     * @param list<KnowledgeSection> $sections List of knowledge sections
     * @param KnowledgeUsage $usage Active plan limits and current usage
     */
    public function __construct(
        public bool $success,
        public array $sections,
        public KnowledgeUsage $usage
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $dataNode = Payload::unwrapData($data);
        $rawSections = Payload::listOfMaps($dataNode, 'sections');
        $sections = array_map(
            static fn(array $sec): KnowledgeSection => KnowledgeSection::fromArray($sec),
            $rawSections
        );

        $usageNode = Payload::map($dataNode, 'usage');
        $usage = KnowledgeUsage::fromArray($usageNode);

        return new self(
            success: Payload::bool($data, 'success', true),
            sections: $sections,
            usage: $usage
        );
    }

    /**
     * @return array{
     *     success: bool,
     *     data: array{
     *         sections: list<array{id: string, position: int, title: string, content: string, isOpen: bool}>,
     *         usage: array<string, int|string>
     *     }
     * }
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => [
                'sections' => array_map(static fn(KnowledgeSection $s): array => $s->toArray(), $this->sections),
                'usage' => $this->usage->toArray(),
            ],
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
