<?php

declare(strict_types=1);

namespace Clusterify\DTO\Chatbot;

/**
 * Visual styling and widget popup settings for the chatbot.
 */
final readonly class ChatbotDetails
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
            title: (string) ($data['title'] ?? 'AI Assistant'),
            headerBgColor: (string) ($data['header_bg_color'] ?? '#222222'),
            headerTextColor: (string) ($data['header_text_color'] ?? '#ffffff'),
            popupRadius: (string) ($data['popup_radius'] ?? '15')
        );
    }

    /**
     * @return array<string, string>
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
}
