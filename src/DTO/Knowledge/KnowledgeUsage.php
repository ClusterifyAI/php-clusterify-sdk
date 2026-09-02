<?php

declare(strict_types=1);

namespace Clusterify\DTO\Knowledge;

/**
 * Plan usage and limits for simple website knowledge base sections.
 */
final readonly class KnowledgeUsage
{
    /**
     * @param int $totalSections Current count of configured sections
     * @param int $maxSections Maximum allowed sections on current plan (15 on Starter, 25 on Pro)
     * @param int $maxSectionLength Maximum character length allowed per section (7000 on Starter, 10000 on Pro)
     * @param int $planId Current plan ID (1: Starter, 2: Professional)
     * @param string $planName Human-readable plan name
     */
    public function __construct(
        public int $totalSections,
        public int $maxSections,
        public int $maxSectionLength,
        public int $planId,
        public string $planName
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            totalSections: (int) ($data['total_sections'] ?? 0),
            maxSections: (int) ($data['max_sections'] ?? 15),
            maxSectionLength: (int) ($data['max_section_length'] ?? 7000),
            planId: (int) ($data['plan_id'] ?? 1),
            planName: (string) ($data['plan_name'] ?? 'Starter Plan')
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'total_sections' => $this->totalSections,
            'max_sections' => $this->maxSections,
            'max_section_length' => $this->maxSectionLength,
            'plan_id' => $this->planId,
            'plan_name' => $this->planName,
        ];
    }
}
