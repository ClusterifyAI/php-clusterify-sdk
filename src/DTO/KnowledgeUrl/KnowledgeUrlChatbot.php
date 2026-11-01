<?php

declare(strict_types=1);

namespace Clusterify\DTO\KnowledgeUrl;

use Clusterify\Support\Payload;
use JsonSerializable;

/**
 * Minimal chatbot reference returned with URL knowledge queries.
 */
final readonly class KnowledgeUrlChatbot implements JsonSerializable
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
            id: Payload::int($data, 'id', 0),
            domain: Payload::string($data, 'domain', '')
        );
    }

    /**
     * @return array{id: int, domain: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'domain' => $this->domain,
        ];
    }

    /**
     * @return array{id: int, domain: string}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
