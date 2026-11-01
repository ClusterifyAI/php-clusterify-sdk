<?php

declare(strict_types=1);

namespace Clusterify\DTO\KnowledgeUrl;

use Clusterify\Support\Payload;
use JsonSerializable;

/**
 * URL-specific knowledge base entry item.
 */
final readonly class KnowledgeUrlItem implements JsonSerializable
{
    /**
     * @param int $id Record integer ID
     * @param int $userId Owner user ID
     * @param int $chatbotId Associated chatbot ID
     * @param string $url Registered webpage URL
     * @param bool $isEnabled Whether this knowledge URL is active
     * @param string $content Markdown documentation content
     * @param bool $hasEmbedding Whether vector embeddings have been computed
     * @param string|null $embeddingAt Embedding computation timestamp
     * @param string|null $createdAt Creation timestamp
     * @param string|null $modifiedAt Last modification timestamp
     */
    public function __construct(
        public int $id,
        public int $userId,
        public int $chatbotId,
        public string $url,
        public bool $isEnabled,
        public string $content,
        public bool $hasEmbedding,
        public ?string $embeddingAt = null,
        public ?string $createdAt = null,
        public ?string $modifiedAt = null
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: Payload::int($data, 'id', 0),
            userId: Payload::int($data, 'user_id', 0),
            chatbotId: Payload::int($data, 'chatbot_id', 0),
            url: Payload::string($data, 'url', ''),
            isEnabled: Payload::bool($data, 'is_enabled', false),
            content: Payload::string($data, 'content', ''),
            hasEmbedding: Payload::bool($data, 'has_embedding', false),
            embeddingAt: Payload::nullableString($data, 'embedding_at'),
            createdAt: Payload::nullableString($data, 'created_at'),
            modifiedAt: Payload::nullableString($data, 'modified_at')
        );
    }

    /**
     * @return array{
     *     id: int,
     *     user_id: int,
     *     chatbot_id: int,
     *     url: string,
     *     is_enabled: bool,
     *     content: string,
     *     has_embedding: bool,
     *     embedding_at: string|null,
     *     created_at: string|null,
     *     modified_at: string|null
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'chatbot_id' => $this->chatbotId,
            'url' => $this->url,
            'is_enabled' => $this->isEnabled,
            'content' => $this->content,
            'has_embedding' => $this->hasEmbedding,
            'embedding_at' => $this->embeddingAt,
            'created_at' => $this->createdAt,
            'modified_at' => $this->modifiedAt,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
