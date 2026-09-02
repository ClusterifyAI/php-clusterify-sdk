<?php

declare(strict_types=1);

namespace Clusterify\DTO\Profile;

/**
 * Tenant subscription plan details and capabilities.
 */
final readonly class PlanData
{
    /**
     * @param int $id Plan level ID (1: Starter, 2: Professional)
     * @param string $name Human-readable plan name (e.g. 'PROFESSIONAL Plan')
     * @param bool $isUrlKnowledgeAllowed Whether URL-based knowledge is unlocked
     * @param PlanRestrictions $restrictions Resource limit matrix
     */
    public function __construct(
        public int $id,
        public string $name,
        public bool $isUrlKnowledgeAllowed,
        public PlanRestrictions $restrictions
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed> $restrictionsData */
        $restrictionsData = $data['restrictions'] ?? [];

        return new self(
            id: (int) ($data['id'] ?? 1),
            name: (string) ($data['name'] ?? 'Starter Plan'),
            isUrlKnowledgeAllowed: (bool) ($data['is_url_knowledge_allowed'] ?? false),
            restrictions: PlanRestrictions::fromArray($restrictionsData)
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_url_knowledge_allowed' => $this->isUrlKnowledgeAllowed,
            'restrictions' => $this->restrictions->toArray(),
        ];
    }
}
