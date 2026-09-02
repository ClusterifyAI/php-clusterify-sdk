<?php

declare(strict_types=1);

namespace Clusterify\Tests\Unit\Resources;

use Clusterify\Http\RequestOptions;
use Clusterify\Resources\ProfileResource;
use Clusterify\Tests\Mocks\MockHttpClient;
use PHPUnit\Framework\TestCase;

final class ProfileResourceTest extends TestCase
{
    public function testGetProfile(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->stub('GET', '/v1/dashboard/profile', [
            'success' => true,
            'data' => [
                'profile' => [
                    'name' => 'Zsolt Szalay',
                    'email' => 'zsolt@clusterify.ai',
                    'country' => 'United States',
                    'timezone' => 'America/New_York',
                ],
                'plan' => [
                    'id' => 2,
                    'name' => 'PROFESSIONAL Plan',
                    'is_url_knowledge_allowed' => true,
                    'restrictions' => [
                        'max_knowledge_general_sections' => 25,
                        'max_knowledge_general_section_length' => 10000,
                        'max_knowledge_urls' => 1000,
                        'max_knowledge_urls_knowledge_length' => 20000,
                        'max_section_title_length' => 100,
                        'max_url_address_length' => 2048,
                    ],
                ],
            ],
        ]);

        $resource = new ProfileResource($mockHttp);
        $res = $resource->get(RequestOptions::noCache());

        $this->assertTrue($res->success);
        $this->assertSame('Zsolt Szalay', $res->profile->name);
        $this->assertSame('zsolt@clusterify.ai', $res->profile->email);
        $this->assertSame(2, $res->plan->id);
        $this->assertSame('PROFESSIONAL Plan', $res->plan->name);
        $this->assertTrue($res->plan->isUrlKnowledgeAllowed);
        $this->assertSame(25, $res->plan->restrictions->maxKnowledgeGeneralSections);
        $this->assertSame(1000, $res->plan->restrictions->maxKnowledgeUrls);
        $this->assertTrue($mockHttp->recordedRequests[0]->options?->noCache);
    }
}
