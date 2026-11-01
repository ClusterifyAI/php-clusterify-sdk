<?php

declare(strict_types=1);

namespace Clusterify\DTO\Chatbot;

use Clusterify\Support\Payload;
use JsonSerializable;

/**
 * Sanitized Chatbot configuration data.
 */
final readonly class ChatbotData implements JsonSerializable
{
    /**
     * @param int $id Chatbot unique integer ID
     * @param string $domain Registered website domain (e.g. 'https://example.com')
     * @param string $publicUuid Public UUID for embedding the widget
     * @param string $aiCompany Underlying AI company (e.g. 'Gemini', 'OpenAI')
     * @param string $aiModel Selected AI model name (e.g. 'gemini-3.8-flash')
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
        $detailsNode = Payload::map($data, 'details');

        return new self(
            id: Payload::int($data, 'id', 0),
            domain: Payload::string($data, 'domain', ''),
            publicUuid: Payload::string($data, 'public_uuid', ''),
            aiCompany: Payload::string($data, 'ai_company', ''),
            aiModel: Payload::string($data, 'ai_model', ''),
            aiThinkingLevel: Payload::string($data, 'ai_thinking_level', 'zero'),
            isEnabled: Payload::bool($data, 'is_enabled', false),
            details: ChatbotDetails::fromArray($detailsNode),
            createdAt: Payload::nullableString($data, 'created_at'),
            modifiedAt: Payload::nullableString($data, 'modified_at')
        );
    }

    /**
     * @return array{
     *     id: int,
     *     domain: string,
     *     public_uuid: string,
     *     ai_company: string,
     *     ai_model: string,
     *     ai_thinking_level: string,
     *     is_enabled: bool,
     *     details: array{title: string, header_bg_color: string, header_text_color: string, popup_radius: string},
     *     created_at: string|null,
     *     modified_at: string|null
     * }
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

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
