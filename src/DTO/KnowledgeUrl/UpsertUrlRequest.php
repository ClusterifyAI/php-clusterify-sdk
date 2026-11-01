<?php

declare(strict_types=1);

namespace Clusterify\DTO\KnowledgeUrl;

use JsonSerializable;

/**
 * Payload for creating or updating a URL knowledge entry (by URL or by record ID).
 */
final readonly class UpsertUrlRequest implements JsonSerializable
{
    /**
     * @param string $url Target webpage URL
     * @param string $content Markdown documentation content
     * @param bool $isEnabled Whether the URL knowledge entry is active
     * @param int|null $id Optional record ID when editing an existing entry by ID
     */
    public function __construct(
        public string $url,
        public string $content,
        public bool $isEnabled = true,
        public ?int $id = null
    ) {}

    /**
     * @return array<string, int|string|bool>
     */
    public function toArray(): array
    {
        $payload = [
            'url' => $this->url,
            'content' => $this->content,
            'is_enabled' => $this->isEnabled,
        ];

        if ($this->id !== null) {
            $payload['id'] = $this->id;
        }

        return $payload;
    }

    /**
     * @return array<string, int|string|bool>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
