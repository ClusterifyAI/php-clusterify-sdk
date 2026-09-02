<?php

declare(strict_types=1);

namespace Clusterify\DTO\Chatbot;

/**
 * Chatbot configuration response envelope.
 */
final readonly class ChatbotResponse
{
    /**
     * @param bool $success Whether the request succeeded
     * @param ChatbotData $chatbot Chatbot configuration
     */
    public function __construct(
        public bool $success,
        public ChatbotData $chatbot
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed> $dataNode */
        $dataNode = $data['data'] ?? $data;
        /** @var array<string, mixed> $chatbotNode */
        $chatbotNode = $dataNode['chatbot'] ?? $dataNode;

        return new self(
            success: (bool) ($data['success'] ?? true),
            chatbot: ChatbotData::fromArray($chatbotNode)
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => [
                'chatbot' => $this->chatbot->toArray(),
            ],
        ];
    }
}
