<?php

declare(strict_types=1);

namespace Clusterify\DTO\Knowledge;

/**
 * Single simple website knowledge base section.
 */
final readonly class KnowledgeSection
{
    /**
     * @param string $id Unique section identifier (e.g. 'kb_1700000000_abcde')
     * @param int $position Zero-based ordering position index
     * @param string $title Section title / category name (e.g. 'Main Context')
     * @param string $content Section markdown knowledge content
     * @param bool $isOpen Whether this knowledge section is active and included in chatbot prompts
     */
    public function __construct(
        public string $id,
        public int $position,
        public string $title,
        public string $content,
        public bool $isOpen
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $isOpen = $data['isOpen'] ?? $data['is_open'] ?? true;

        return new self(
            id: (string) ($data['id'] ?? ''),
            position: (int) ($data['position'] ?? 0),
            title: (string) ($data['title'] ?? ''),
            content: (string) ($data['content'] ?? ''),
            isOpen: (bool) $isOpen
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'position' => $this->position,
            'title' => $this->title,
            'content' => $this->content,
            'isOpen' => $this->isOpen,
        ];
    }
}
