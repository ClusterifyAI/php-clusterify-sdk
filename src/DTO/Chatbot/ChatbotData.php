<?php

declare(strict_types=1);

namespace Clusterify\DTO\Chatbot;

/**
 * Sanitized Chatbot configuration data.
 */
final readonly class ChatbotData
{
    /**
     * @param int $id Chatbot unique integer ID
     * @param string $domain Registered website domain (e.g. 'https://example.com')
     * @param string $publicUuid Public UUID for embedding the widget
     * @param string $aiCompany Underlying AI company (e.g. 'Gemini', 'OpenAI')
     * @param string $aiModel Selected AI model name (e.g. 'gemini-3.7-flash')
     * @param string $aiThinkingLevel AI reasoning level (e.g. 'zero', 'low', 'medium', 'high')
     * @param bool $isEnabled Whether the chatbot widget is active
     * @param ChatbotDetails $details Visual and theme styling configuration
     * @param string|null $createdAt ISO-8601 creation timestamp
     * @param string|null $modifiedAt ISO-8601 last modification timestamp
     */
    public function __construct(
        public int $id,
        public string $domain,
        public string $publicUuid,
        public string $aiCompany,
        public string $aiModel,
        public string $aiThinkingLevel,
        public bool $isEnabled,
        public ChatbotDetails $details,
        public ?string $createdAt = null,
        public ?string $modifiedAt = null
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed> $detailsNode */
        $detailsNode = $data['details'] ?? [];

        return new self(
            id: (int) ($data['id'] ?? 0),
            domain: (string) ($data['domain'] ?? ''),
            publicUuid: (string) ($data['public_uuid'] ?? ''),
            aiCompany: (string) ($data['ai_company'] ?? ''),
            aiModel: (string) ($data['ai_model'] ?? ''),
            aiThinkingLevel: (string) ($data['ai_thinking_level'] ?? 'zero'),
            isEnabled: (bool) ($data['is_enabled'] ?? false),
            details: ChatbotDetails::fromArray($detailsNode),
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
            'domain' => $this->domain,
            'public_uuid' => $this->publicUuid,
            'ai_company' => $this->aiCompany,
            'ai_model' => $this->aiModel,
            'ai_thinking_level' => $this->aiThinkingLevel,
            'is_enabled' => $this->isEnabled,
            'details' => $this->details->toArray(),
            'created_at' => $this->createdAt,
            'modified_at' => $this->modifiedAt,
        ];
    }
}
