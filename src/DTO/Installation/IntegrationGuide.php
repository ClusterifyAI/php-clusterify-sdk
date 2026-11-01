<?php

declare(strict_types=1);

namespace Clusterify\DTO\Installation;

use Clusterify\Support\Payload;
use JsonSerializable;

/**
 * Platform-specific chatbot installation guide.
 */
final readonly class IntegrationGuide implements JsonSerializable
{
    /**
     * @param string $platform Platform code identifier (e.g. 'html', 'nextjs', 'wordpress', 'shopify', 'gtm', 'magento2')
     * @param string $name Human-readable platform name (e.g. 'HTML / Any Website')
     * @param string $summary Brief integration instructions summary
     * @param string $docUrl Documentation URL for full step-by-step tutorial
     */
    public function __construct(
        public string $platform,
        public string $name,
        public string $summary,
        public string $docUrl
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            platform: Payload::string($data, 'platform', ''),
            name: Payload::string($data, 'name', ''),
            summary: Payload::string($data, 'summary', ''),
            docUrl: Payload::string($data, 'doc_url', '')
        );
    }

    /**
     * @return array{platform: string, name: string, summary: string, doc_url: string}
     */
    public function toArray(): array
    {
        return [
            'platform' => $this->platform,
            'name' => $this->name,
            'summary' => $this->summary,
            'doc_url' => $this->docUrl,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
