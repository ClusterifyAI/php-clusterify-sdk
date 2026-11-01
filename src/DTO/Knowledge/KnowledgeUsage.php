<?php

declare(strict_types=1);

namespace Clusterify\DTO\Knowledge;

use Clusterify\Support\Payload;
use JsonSerializable;

/**
 * Plan usage and limits for simple website knowledge base sections.
 */
final readonly class KnowledgeUsage implements JsonSerializable
{
    /**
     * @param int $totalSections Current count of configured sections
     * @param int $maxSections Maximum allowed sections on current plan (15 on Starter, 25 on Pro)
     * @param int $maxSectionLength Maximum character length allowed per section (7000 on Starter, 10000 on Pro)
     * @param int $planId Current plan ID (1: Starter, 2: Professional)
     * @param string $planName Human-readable plan name
     * @param int $maxSectionTitleLength Maximum character length allowed for section titles (100)
     */
    public function __construct(
        public int $totalSections,
        public int $maxSections,
        public int $maxSectionLength,
        public int $planId,
        public string $planName,
        public int $maxSectionTitleLength = 100
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            totalSections: Payload::int($data, 'total_sections', 0),
            maxSections: Payload::int($data, 'max_sections', 15),
            maxSectionLength: Payload::int($data, 'max_section_length', 7000),
            planId: Payload::int($data, 'plan_id', 1),
            planName: Payload::string($data, 'plan_name', 'Starter Plan'),
            maxSectionTitleLength: Payload::int($data, 'max_section_title_length', 100)
        );
    }

    /**
     * @return array{
     *     total_sections: int,
     *     max_sections: int,
     *     max_section_length: int,
     *     max_section_title_length: int,
     *     plan_id: int,
     *     plan_name: string
     * }
     */
    public function toArray(): array
    {
        return [
            'total_sections' => $this->totalSections,
            'max_sections' => $this->maxSections,
            'max_section_length' => $this->maxSectionLength,
            'max_section_title_length' => $this->maxSectionTitleLength,
            'plan_id' => $this->planId,
            'plan_name' => $this->planName,
        ];
    }

    /**
     * @return array<string, int|string>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
