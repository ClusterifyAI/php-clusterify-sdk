<?php

declare(strict_types=1);

namespace Clusterify\DTO\KnowledgeUrl;

/**
 * URL-specific knowledge base entry item.
 */
final readonly class KnowledgeUrlItem
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
            id: (int) ($data['id'] ?? 0),
            userId: (int) ($data['user_id'] ?? 0),
            chatbotId: (int) ($data['chatbot_id'] ?? 0),
            url: (string) ($data['url'] ?? ''),
            isEnabled: (bool) ($data['is_enabled'] ?? false),
            content: (string) ($data['content'] ?? ''),
            hasEmbedding: (bool) ($data['has_embedding'] ?? false),
            embeddingAt: isset($data['embedding_at']) && is_string($data['embedding_at']) ? $data['embedding_at'] : null,
            createdAt: isset($data['created_at']) && is_string($data['created_at']) ? $data['created_at'] : null,
            modifiedAt: isset($data['modified_at']) && is_string($data['modified_at']) ? $data['modified_at'] : null
        );
    }

    /**
     * @return array<string, mixed>
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
}
