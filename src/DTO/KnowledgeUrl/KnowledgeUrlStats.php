<?php

declare(strict_types=1);

namespace Clusterify\DTO\KnowledgeUrl;

/**
 * Aggregate metrics and capacity statistics for URL-based knowledge.
 */
final readonly class KnowledgeUrlStats
{
    /**
     * @param int $total Total URL knowledge entries registered
     * @param int $enabled Total active/enabled URLs
     * @param int $disabled Total disabled URLs
     * @param int $maxUrls Maximum allowed URLs for current plan
     * @param int $maxContentLength Maximum character length allowed per URL content
     */
    public function __construct(
        public int $total,
        public int $enabled,
        public int $disabled,
        public int $maxUrls,
        public int $maxContentLength
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            total: (int) ($data['total'] ?? 0),
            enabled: (int) ($data['enabled'] ?? 0),
            disabled: (int) ($data['disabled'] ?? 0),
            maxUrls: (int) ($data['max_urls'] ?? 1000),
            maxContentLength: (int) ($data['max_content_length'] ?? 20000)
        );
    }

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'enabled' => $this->enabled,
            'disabled' => $this->disabled,
            'max_urls' => $this->maxUrls,
            'max_content_length' => $this->maxContentLength,
        ];
    }
}
