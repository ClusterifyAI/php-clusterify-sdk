<?php

declare(strict_types=1);

namespace Clusterify\DTO\Knowledge;

/**
 * Knowledge base listing response envelope containing sections array and plan usage.
 */
final readonly class KnowledgeListResponse
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
        /** @var array<string, mixed> $dataNode */
        $dataNode = $data['data'] ?? $data;
        /** @var list<array<string, mixed>> $rawSections */
        $rawSections = $dataNode['sections'] ?? [];
        $sections = array_values(array_map(
            static fn(array $sec): KnowledgeSection => KnowledgeSection::fromArray($sec),
            $rawSections
        ));

        /** @var array<string, mixed> $usageNode */
        $usageNode = $dataNode['usage'] ?? [];
        $usage = KnowledgeUsage::fromArray($usageNode);

        return new self(
            success: (bool) ($data['success'] ?? true),
            sections: $sections,
            usage: $usage
        );
    }

    /**
     * @return array<string, mixed>
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
}
