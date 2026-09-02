<?php

declare(strict_types=1);

namespace Clusterify\DTO\Installation;

/**
 * Installation snippet and guides response envelope.
 */
final readonly class InstallationResponse
{
    /**
     * @param bool $success Whether the request succeeded
     * @param string $publicUuid Public UUID for the Chatbot
     * @param string $domain Registered website domain
     * @param bool $isEnabled Whether the chatbot is currently enabled
     * @param bool $hasChatbot Whether the account has a chatbot configured
     * @param string $htmlSnippet Ready-to-use HTML script loader code snippet
     * @param InstallationInfo $information Comprehensive platform guides and admin routes
     */
    public function __construct(
        public bool $success,
        public string $publicUuid,
        public string $domain,
        public bool $isEnabled,
        public bool $hasChatbot,
        public string $htmlSnippet,
        public InstallationInfo $information
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed> $dataNode */
        $dataNode = $data['data'] ?? $data;
        /** @var array<string, mixed> $infoNode */
        $infoNode = $dataNode['information'] ?? [];

        return new self(
            success: (bool) ($data['success'] ?? true),
            publicUuid: (string) ($dataNode['public_uuid'] ?? ''),
            domain: (string) ($dataNode['domain'] ?? ''),
            isEnabled: (bool) ($dataNode['is_enabled'] ?? false),
            hasChatbot: (bool) ($dataNode['has_chatbot'] ?? false),
            htmlSnippet: (string) ($dataNode['html_snippet'] ?? ''),
            information: InstallationInfo::fromArray($infoNode)
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
                'public_uuid' => $this->publicUuid,
                'domain' => $this->domain,
                'is_enabled' => $this->isEnabled,
                'has_chatbot' => $this->hasChatbot,
                'html_snippet' => $this->htmlSnippet,
                'information' => $this->information->toArray(),
            ],
        ];
    }
}
