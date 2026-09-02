<?php

declare(strict_types=1);

namespace Clusterify\DTO\Profile;

/**
 * Resource limits and restrictions associated with the user's plan.
 */
final readonly class PlanRestrictions
{
    /**
     * @param int $maxKnowledgeGeneralSections Max simple KB sections (15 on Starter, 25 on Pro)
     * @param int $maxKnowledgeGeneralSectionLength Max characters per simple KB section (7K on Starter, 10K on Pro)
     * @param int $maxKnowledgeUrls Max URL knowledge entries (0 on Starter, 1000 on Pro)
     * @param int $maxKnowledgeUrlsKnowledgeLength Max characters per URL knowledge entry (20K on Pro)
     * @param int $maxSectionTitleLength Max character length for section title (100)
     * @param int $maxUrlAddressLength Max length for URL address (2048)
     */
    public function __construct(
        public int $maxKnowledgeGeneralSections,
        public int $maxKnowledgeGeneralSectionLength,
        public int $maxKnowledgeUrls,
        public int $maxKnowledgeUrlsKnowledgeLength,
        public int $maxSectionTitleLength,
        public int $maxUrlAddressLength
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            maxKnowledgeGeneralSections: (int) ($data['max_knowledge_general_sections'] ?? 15),
            maxKnowledgeGeneralSectionLength: (int) ($data['max_knowledge_general_section_length'] ?? 7000),
            maxKnowledgeUrls: (int) ($data['max_knowledge_urls'] ?? 0),
            maxKnowledgeUrlsKnowledgeLength: (int) ($data['max_knowledge_urls_knowledge_length'] ?? 0),
            maxSectionTitleLength: (int) ($data['max_section_title_length'] ?? 100),
            maxUrlAddressLength: (int) ($data['max_url_address_length'] ?? 2048)
        );
    }

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'max_knowledge_general_sections' => $this->maxKnowledgeGeneralSections,
            'max_knowledge_general_section_length' => $this->maxKnowledgeGeneralSectionLength,
            'max_knowledge_urls' => $this->maxKnowledgeUrls,
            'max_knowledge_urls_knowledge_length' => $this->maxKnowledgeUrlsKnowledgeLength,
            'max_section_title_length' => $this->maxSectionTitleLength,
            'max_url_address_length' => $this->maxUrlAddressLength,
        ];
    }
}
