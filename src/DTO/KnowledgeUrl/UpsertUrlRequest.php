<?php

declare(strict_types=1);

namespace Clusterify\DTO\KnowledgeUrl;

/**
 * Payload for creating or updating a URL knowledge entry.
 */
final readonly class UpsertUrlRequest
{
    /**
     * @param string $url Target webpage URL
     * @param string $content Markdown documentation content
     * @param bool $isEnabled Whether the URL knowledge entry is active
     */
    public function __construct(
        public string $url,
        public string $content,
        public bool $isEnabled = true
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'content' => $this->content,
            'is_enabled' => $this->isEnabled,
        ];
    }
}
