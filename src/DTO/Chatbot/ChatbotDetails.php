<?php

declare(strict_types=1);

namespace Clusterify\DTO\Chatbot;

use Clusterify\Support\Payload;
use JsonSerializable;

/**
 * Visual styling and widget popup settings for the chatbot.
 */
final readonly class ChatbotDetails implements JsonSerializable
{
    /**
     * @param string $title Chatbot popup window title (e.g. 'AI Assistant')
     * @param string $headerBgColor Header background hex color (e.g. '#222222')
     * @param string $headerTextColor Header text hex color (e.g. '#ffffff')
     * @param string $popupRadius Widget corner radius in pixels (e.g. '15')
     */
    public function __construct(
        public string $title,
        public string $headerBgColor,
        public string $headerTextColor,
        public string $popupRadius
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: Payload::string($data, 'title', 'AI Assistant'),
            headerBgColor: Payload::string($data, 'header_bg_color', '#222222'),
            headerTextColor: Payload::string($data, 'header_text_color', '#ffffff'),
            popupRadius: Payload::string($data, 'popup_radius', '15')
        );
    }

    /**
     * @return array{title: string, header_bg_color: string, header_text_color: string, popup_radius: string}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'header_bg_color' => $this->headerBgColor,
            'header_text_color' => $this->headerTextColor,
            'popup_radius' => $this->popupRadius,
        ];
    }

    /**
     * @return array{title: string, header_bg_color: string, header_text_color: string, popup_radius: string}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
