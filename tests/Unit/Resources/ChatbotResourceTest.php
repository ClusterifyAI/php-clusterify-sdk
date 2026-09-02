<?php

declare(strict_types=1);

namespace Clusterify\Tests\Unit\Resources;

use Clusterify\Resources\ChatbotResource;
use Clusterify\Tests\Mocks\MockHttpClient;
use PHPUnit\Framework\TestCase;

final class ChatbotResourceTest extends TestCase
{
    public function testGetChatbot(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->stub('GET', '/v1/dashboard/chatbot', [
            'success' => true,
            'data' => [
                'chatbot' => [
                    'id' => 50,
                    'domain' => 'https://example.com',
                    'public_uuid' => '78d2c63c-2c11-4d4b-adec-63dbdfb5c60e',
                    'ai_company' => 'Gemini',
                    'ai_model' => 'gemini-3.7-flash',
                    'ai_thinking_level' => 'zero',
                    'is_enabled' => true,
                    'details' => [
                        'title' => 'AI Assistant',
                        'header_bg_color' => '#222222',
                        'header_text_color' => '#ffffff',
                        'popup_radius' => '15',
                    ],
                    'created_at' => '2026-03-01T10:00:00.000Z',
                    'modified_at' => '2026-03-12T14:30:00.000Z',
                ],
            ],
        ]);

        $resource = new ChatbotResource($mockHttp);
        $res = $resource->get();

        $this->assertTrue($res->success);
        $this->assertSame(50, $res->chatbot->id);
        $this->assertSame('https://example.com', $res->chatbot->domain);
        $this->assertSame('78d2c63c-2c11-4d4b-adec-63dbdfb5c60e', $res->chatbot->publicUuid);
        $this->assertSame('Gemini', $res->chatbot->aiCompany);
        $this->assertSame('gemini-3.7-flash', $res->chatbot->aiModel);
        $this->assertSame('AI Assistant', $res->chatbot->details->title);
        $this->assertSame('#222222', $res->chatbot->details->headerBgColor);
        $this->assertSame('15', $res->chatbot->details->popupRadius);
    }
}
