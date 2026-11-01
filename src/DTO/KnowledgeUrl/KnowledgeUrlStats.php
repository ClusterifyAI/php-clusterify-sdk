<?php

declare(strict_types=1);

namespace Clusterify\DTO\KnowledgeUrl;

use Clusterify\Support\Payload;
use JsonSerializable;

/**
 * Aggregate metrics and capacity statistics for URL-based knowledge.
 */
final readonly class KnowledgeUrlStats implements JsonSerializable
{
    /**
     * @param int $total Total URL knowledge entries registered
     * @param int $enabled Total active/enabled URLs
     * @param int $disabled Total disabled URLs
     * @param int $maxUrls Maximum allowed URLs for current plan
     * @param int $maxContentLength Maximum character length allowed per URL content
     * @param int $maxUrlAddressLength Maximum character length allowed per URL address (2048)
     */
    public function __construct(
        public int $total,
        public int $enabled,
        public int $disabled,
        public int $maxUrls,
        public int $maxContentLength,
        public int $maxUrlAddressLength = 2048
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            total: Payload::int($data, 'total', 0),
            enabled: Payload::int($data, 'enabled', 0),
            disabled: Payload::int($data, 'disabled', 0),
            maxUrls: Payload::int($data, 'max_urls', 1000),
            maxContentLength: Payload::int($data, 'max_content_length', 20000),
            maxUrlAddressLength: Payload::int($data, 'max_url_address_length', 2048)
        );
    }

    /**
     * @return array{
     *     total: int,
     *     enabled: int,
     *     disabled: int,
     *     max_urls: int,
     *     max_content_length: int,
     *     max_url_address_length: int
     * }
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'enabled' => $this->enabled,
            'disabled' => $this->disabled,
            'max_urls' => $this->maxUrls,
            'max_content_length' => $this->maxContentLength,
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
