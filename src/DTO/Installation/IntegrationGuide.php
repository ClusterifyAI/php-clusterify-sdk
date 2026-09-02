<?php

declare(strict_types=1);

namespace Clusterify\DTO\Installation;

/**
 * Platform-specific chatbot installation guide.
 */
final readonly class IntegrationGuide
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
            platform: (string) ($data['platform'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            summary: (string) ($data['summary'] ?? ''),
            docUrl: (string) ($data['doc_url'] ?? '')
        );
    }

    /**
     * @return array<string, string>
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
}
