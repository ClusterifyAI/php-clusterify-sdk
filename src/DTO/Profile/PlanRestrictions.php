<?php

declare(strict_types=1);

namespace Clusterify\DTO\Profile;

use Clusterify\Support\Payload;
use JsonSerializable;

/**
 * Resource limits and restrictions associated with the user's plan.
 */
final readonly class PlanRestrictions implements JsonSerializable
{
    /**
     * @param int $maxKnowledgeGeneralSections Max simple KB sections (15 on Starter, 25 on Pro)
     * @param int $maxKnowledgeGeneralSectionLength Max characters per simple KB section (7K on Starter, 10K on Pro)
     * @param int $maxKnowledgeUrls Max URL knowledge entries (0 on Starter, 1000 on Pro)
     * @param int $maxKnowledgeUrlsKnowledgeLength Max characters per URL knowledge entry (20K on Pro)
     * @param int $maxSectionTitleLength Max character length for section title (100)
     * @param int $maxUrlAddressLength Max length for URL address (2048)
     * @param int $apiCallsPerMinute Max API requests per minute (60 on Starter, 120 on Pro)
     * @param int $apiCallsPerDay Max API requests per day (8000 on Starter, 50000 on Pro)
     */
    public function __construct(
        public int $maxKnowledgeGeneralSections,
        public int $maxKnowledgeGeneralSectionLength,
        public int $maxKnowledgeUrls,
        public int $maxKnowledgeUrlsKnowledgeLength,
        public int $maxSectionTitleLength,
        public int $maxUrlAddressLength,
        public int $apiCallsPerMinute = 60,
        public int $apiCallsPerDay = 8000
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            maxKnowledgeGeneralSections: Payload::int($data, 'max_knowledge_general_sections', 15),
            maxKnowledgeGeneralSectionLength: Payload::int($data, 'max_knowledge_general_section_length', 7000),
            maxKnowledgeUrls: Payload::int($data, 'max_knowledge_urls', 0),
            maxKnowledgeUrlsKnowledgeLength: Payload::int($data, 'max_knowledge_urls_knowledge_length', 0),
            maxSectionTitleLength: Payload::int($data, 'max_section_title_length', 100),
            maxUrlAddressLength: Payload::int($data, 'max_url_address_length', 2048),
            apiCallsPerMinute: Payload::int($data, 'api_calls_per_minute', 60),
            apiCallsPerDay: Payload::int($data, 'api_calls_per_day', 8000)
        );
    }

    /**
     * @return array{
     *     api_calls_per_minute: int,
     *     api_calls_per_day: int,
     *     max_knowledge_general_sections: int,
     *     max_knowledge_general_section_length: int,
     *     max_knowledge_urls: int,
     *     max_knowledge_urls_knowledge_length: int,
     *     max_section_title_length: int,
     *     max_url_address_length: int
     * }
     */
    public function toArray(): array
    {
        return [
            'api_calls_per_minute' => $this->apiCallsPerMinute,
            'api_calls_per_day' => $this->apiCallsPerDay,
            'max_knowledge_general_sections' => $this->maxKnowledgeGeneralSections,
            'max_knowledge_general_section_length' => $this->maxKnowledgeGeneralSectionLength,
            'max_knowledge_urls' => $this->maxKnowledgeUrls,
            'max_knowledge_urls_knowledge_length' => $this->maxKnowledgeUrlsKnowledgeLength,
            'max_section_title_length' => $this->maxSectionTitleLength,
            'max_url_address_length' => $this->maxUrlAddressLength,
        ];
    }

    /**
     * @return array<string, int>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
