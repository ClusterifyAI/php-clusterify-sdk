<?php

declare(strict_types=1);

namespace Clusterify\DTO\Knowledge;

use JsonSerializable;

/**
 * Request payload for updating an existing knowledge base section.
 */
final readonly class UpdateSectionRequest implements JsonSerializable
{
    /**
     * @param string $id Target section ID
     * @param string|null $title Updated title (null to keep current)
     * @param string|null $content Updated markdown content (null to keep current)
     * @param bool|null $isOpen Updated active status (null to keep current)
     */
    public function __construct(
        public string $id,
        public ?string $title = null,
        public ?string $content = null,
        public ?bool $isOpen = null
    ) {}

    /**
     * @return array<string, string|bool>
     */
    public function toArray(): array
    {
        $payload = ['id' => $this->id];

        if ($this->title !== null) {
            $payload['title'] = $this->title;
        }

        if ($this->content !== null) {
            $payload['content'] = $this->content;
        }

        if ($this->isOpen !== null) {
            $payload['is_open'] = $this->isOpen;
        }

        return $payload;
    }

    /**
     * @return array<string, string|bool>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
