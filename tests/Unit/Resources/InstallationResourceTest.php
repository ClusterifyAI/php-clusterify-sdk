<?php

declare(strict_types=1);

namespace Clusterify\Tests\Unit\Resources;

use Clusterify\Resources\InstallationResource;
use Clusterify\Tests\Mocks\MockHttpClient;
use PHPUnit\Framework\TestCase;

final class InstallationResourceTest extends TestCase
{
    public function testGetInstallation(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->stub('GET', '/v1/dashboard/installation', [
            'success' => true,
            'data' => [
                'public_uuid' => '78d2c63c-2c11-4d4b-adec-63dbdfb5c60e',
                'domain' => 'https://example.com',
                'is_enabled' => true,
                'has_chatbot' => true,
                'html_snippet' => '<script id="clusterify-chatbot-script"></script>',
                'information' => [
                    'overview' => 'Embed the HTML snippet directly before </body>',
                    'available_guides' => [
                        [
                            'platform' => 'html',
                            'name' => 'HTML / Any Website',
                            'summary' => 'Copy the HTML snippet',
                            'doc_url' => 'https://dashboard.clusterify.ai/installation',
                        ],
                    ],
                    'admin_guides' => [
                        'installation_page' => '/installation',
                        'chatbot_settings_page' => '/chatbot',
                        'styling_builder_page' => '/chatbot-builder',
                        'integrations_page' => '/integrations',
                        'api_keys_page' => '/api-key',
                    ],
                    'verification_steps' => [
                        '1. Paste HTML',
                        '2. Verify domain',
                    ],
                    'security_notice' => 'Public UUID is safe to expose in client-side HTML.',
                ],
            ],
        ]);

        $resource = new InstallationResource($mockHttp);
        $res = $resource->get(hostSystem: 'magento2');

        $this->assertTrue($res->success);
        $this->assertSame('78d2c63c-2c11-4d4b-adec-63dbdfb5c60e', $res->publicUuid);
        $this->assertSame('https://example.com', $res->domain);
        $this->assertTrue($res->isEnabled);
        $this->assertTrue($res->hasChatbot);
        $this->assertSame('<script id="clusterify-chatbot-script"></script>', $res->htmlSnippet);
        $this->assertCount(1, $res->information->availableGuides);
        $this->assertSame('html', $res->information->availableGuides[0]->platform);
        $this->assertSame('/installation', $res->information->adminGuides->installationPage);
        $this->assertCount(2, $res->information->verificationSteps);
        $this->assertSame(['host_system' => 'magento2'], $mockHttp->recordedRequests[0]->queryParams);
    }
}
