<?php

declare(strict_types=1);

namespace Clusterify\DTO\Installation;

/**
 * Direct links to relevant Clusterify dashboard administration pages.
 */
final readonly class AdminGuides
{
    /**
     * @param string $installationPage Installation page route (e.g. '/installation')
     * @param string $chatbotSettingsPage Chatbot configuration page route (e.g. '/chatbot')
     * @param string $stylingBuilderPage Visual builder page route (e.g. '/chatbot-builder')
     * @param string $integrationsPage Integrations page route (e.g. '/integrations')
     * @param string $apiKeysPage API keys management route (e.g. '/api-key')
     */
    public function __construct(
        public string $installationPage,
        public string $chatbotSettingsPage,
        public string $stylingBuilderPage,
        public string $integrationsPage,
        public string $apiKeysPage
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            installationPage: (string) ($data['installation_page'] ?? '/installation'),
            chatbotSettingsPage: (string) ($data['chatbot_settings_page'] ?? '/chatbot'),
            stylingBuilderPage: (string) ($data['styling_builder_page'] ?? '/chatbot-builder'),
            integrationsPage: (string) ($data['integrations_page'] ?? '/integrations'),
            apiKeysPage: (string) ($data['api_keys_page'] ?? '/api-key')
        );
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'installation_page' => $this->installationPage,
            'chatbot_settings_page' => $this->chatbotSettingsPage,
            'styling_builder_page' => $this->stylingBuilderPage,
            'integrations_page' => $this->integrationsPage,
            'api_keys_page' => $this->apiKeysPage,
        ];
    }
}
