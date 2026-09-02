<?php

declare(strict_types=1);

namespace Clusterify\DTO\KnowledgeUrl;

/**
 * Minimal chatbot reference returned with URL knowledge queries.
 */
final readonly class KnowledgeUrlChatbot
{
    /**
     * @param int $id Chatbot ID
     * @param string $domain Chatbot registered domain
     */
    public function __construct(
        public int $id,
        public string $domain
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            domain: (string) ($data['domain'] ?? '')
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'domain' => $this->domain,
        ];
    }
}
