<?php

declare(strict_types=1);

namespace Clusterify\DTO\Knowledge;

use JsonSerializable;

/**
 * Request payload for creating a new simple knowledge base section.
 */
final readonly class CreateSectionRequest implements JsonSerializable
{
    /**
     * @param string $title Section title (max 100 characters)
     * @param string $content Section markdown content
     * @param bool $isOpen Whether the section is active
     */
    public function __construct(
        public string $title,
        public string $content,
        public bool $isOpen = true
    ) {}

    /**
     * @return array{title: string, content: string, is_open: bool}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'is_open' => $this->isOpen,
        ];
    }

    /**
     * @return array{title: string, content: string, is_open: bool}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
