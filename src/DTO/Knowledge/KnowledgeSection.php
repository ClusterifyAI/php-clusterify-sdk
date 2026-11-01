<?php

declare(strict_types=1);

namespace Clusterify\DTO\Knowledge;

use Clusterify\Support\Payload;
use JsonSerializable;

/**
 * Single simple website knowledge base section.
 */
final readonly class KnowledgeSection implements JsonSerializable
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
        $isOpen = array_key_exists('isOpen', $data)
            ? Payload::bool($data, 'isOpen', true)
            : Payload::bool($data, 'is_open', true);

        return new self(
            id: Payload::string($data, 'id', ''),
            position: Payload::int($data, 'position', 0),
            title: Payload::string($data, 'title', ''),
            content: Payload::string($data, 'content', ''),
            isOpen: $isOpen
        );
    }

    /**
     * @return array{id: string, position: int, title: string, content: string, isOpen: bool}
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

    /**
     * @return array{id: string, position: int, title: string, content: string, isOpen: bool}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
