<?php

declare(strict_types=1);

namespace Clusterify\DTO\Chatbot;

use Clusterify\Support\Payload;
use JsonSerializable;

/**
 * Chatbot configuration response envelope.
 */
final readonly class ChatbotResponse implements JsonSerializable
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
        $dataNode = Payload::unwrapData($data);
        $chatbotNode = array_key_exists('chatbot', $dataNode) && is_array($dataNode['chatbot'])
            ? Payload::map($dataNode, 'chatbot')
            : $dataNode;

        return new self(
            success: Payload::bool($data, 'success', true),
            chatbot: ChatbotData::fromArray($chatbotNode)
        );
    }

    /**
     * @return array{success: bool, data: array{chatbot: array<string, mixed>}}
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

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
