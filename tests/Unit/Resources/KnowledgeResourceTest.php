<?php

declare(strict_types=1);

namespace Clusterify\Tests\Unit\Resources;

use Clusterify\DTO\Knowledge\CreateSectionRequest;
use Clusterify\Exceptions\ResourceNotFoundException;
use Clusterify\Resources\KnowledgeResource;
use Clusterify\Tests\Mocks\MockHttpClient;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class KnowledgeResourceTest extends TestCase
{
    public function testList(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->stub('GET', '/v1/dashboard/knowledge', [
            'success' => true,
            'data' => [
                'sections' => [
                    [
                        'id' => 'kb_1700000000_abcde',
                        'position' => 0,
                        'title' => 'Main Context',
                        'content' => 'General business info',
                        'isOpen' => true,
                    ],
                ],
                'usage' => [
                    'total_sections' => 1,
                    'max_sections' => 25,
                    'max_section_length' => 10000,
                    'max_section_title_length' => 100,
                    'plan_id' => 2,
                    'plan_name' => 'PROFESSIONAL Plan',
                ],
            ],
        ]);

        $resource = new KnowledgeResource($mockHttp);
        $res = $resource->list('Main');

        $this->assertTrue($res->success);
        $this->assertCount(1, $res->sections);
        $this->assertSame('kb_1700000000_abcde', $res->sections[0]->id);
        $this->assertSame('Main Context', $res->sections[0]->title);
        $this->assertSame(1, $res->usage->totalSections);
        $this->assertSame(25, $res->usage->maxSections);
        $this->assertSame(100, $res->usage->maxSectionTitleLength);
        $this->assertSame(['search' => 'Main'], $mockHttp->recordedRequests[0]->queryParams);
    }

    public function testGetSuccess(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->stub('GET', '/v1/dashboard/knowledge', [
            'success' => true,
            'data' => [
                'sections' => [
                    [
                        'id' => 'kb_1700000000_abcde',
                        'position' => 0,
                        'title' => 'Main Context',
                        'content' => 'Context content',
                        'isOpen' => true,
                    ],
                ],
            ],
        ]);

        $resource = new KnowledgeResource($mockHttp);
        $section = $resource->get('kb_1700000000_abcde');

        $this->assertSame('kb_1700000000_abcde', $section->id);
        $this->assertSame('Main Context', $section->title);
    }

    public function testGetNotFoundThrows(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->stub('GET', '/v1/dashboard/knowledge', [
            'success' => true,
            'data' => [
                'sections' => [],
            ],
        ]);

        $resource = new KnowledgeResource($mockHttp);
        $this->expectException(ResourceNotFoundException::class);
        $resource->get('non_existent_id');
    }

    public function testCreate(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->stub('POST', '/v1/dashboard/knowledge', [
            'success' => true,
            'data' => [
                'section' => [
                    'id' => 'kb_new_12345',
                    'position' => 2,
                    'title' => 'Return Policy',
                    'content' => 'Returns within 30 days.',
                    'isOpen' => true,
                ],
            ],
        ], 201);

        $resource = new KnowledgeResource($mockHttp);
        $section = $resource->create('Return Policy', 'Returns within 30 days.');

        $this->assertSame('kb_new_12345', $section->id);
        $this->assertSame('Return Policy', $section->title);
        $this->assertSame('Returns within 30 days.', $section->content);
        $this->assertTrue($section->isOpen);
    }

    public function testCreateThrowsWithoutContentWhenTitleIsString(): void
    {
        $resource = new KnowledgeResource(new MockHttpClient());
        $this->expectException(InvalidArgumentException::class);
        $resource->create('Return Policy');
    }

    public function testUpdate(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->stub('POST', '/v1/dashboard/knowledge', [
            'success' => true,
            'data' => [
                'section' => [
                    'id' => 'kb_123',
                    'position' => 0,
                    'title' => 'Updated Title',
                    'content' => 'Updated Content',
                    'isOpen' => false,
                ],
            ],
        ]);

        $resource = new KnowledgeResource($mockHttp);
        $section = $resource->update('kb_123', 'Updated Title', 'Updated Content', false);

        $this->assertSame('Updated Title', $section->title);
        $this->assertFalse($section->isOpen);
    }

    public function testPatch(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->stub('PATCH', '/v1/dashboard/knowledge/kb_123', [
            'success' => true,
            'data' => [
                'section' => [
                    'id' => 'kb_123',
                    'position' => 0,
                    'title' => 'Patched Title',
                    'content' => 'Content',
                    'isOpen' => true,
                ],
            ],
        ]);

        $resource = new KnowledgeResource($mockHttp);
        $section = $resource->patch('kb_123', ['title' => 'Patched Title']);

        $this->assertSame('Patched Title', $section->title);
        $this->assertSame('PATCH', $mockHttp->recordedRequests[0]->method);
        $this->assertSame('/v1/dashboard/knowledge/kb_123', $mockHttp->recordedRequests[0]->path);
    }

    public function testPatchThrowsOnEmptyAttributes(): void
    {
        $resource = new KnowledgeResource(new MockHttpClient());
        $this->expectException(InvalidArgumentException::class);
        $resource->patch('kb_123', []);
    }

    public function testToggle(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->stub('POST', '/v1/dashboard/knowledge', [
            'success' => true,
            'data' => [
                'section' => [
                    'id' => 'kb_123',
                    'position' => 0,
                    'title' => 'Title',
                    'content' => 'Content',
                    'isOpen' => false,
                ],
            ],
        ]);

        $resource = new KnowledgeResource($mockHttp);
        $section = $resource->toggle('kb_123', false);

        $this->assertFalse($section->isOpen);
        $this->assertSame([
            'action' => 'toggle',
            'id' => 'kb_123',
            'is_open' => false,
        ], $mockHttp->recordedRequests[0]->body);
    }

    public function testReorder(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->stub('POST', '/v1/dashboard/knowledge', [
            'success' => true,
            'message' => 'Reordered successfully',
            'data' => [
                'sections' => [
                    ['id' => 'kb_2', 'position' => 0, 'title' => 'Two', 'content' => '2', 'isOpen' => true],
                    ['id' => 'kb_1', 'position' => 1, 'title' => 'One', 'content' => '1', 'isOpen' => true],
                ],
            ],
        ]);

        $resource = new KnowledgeResource($mockHttp);
        $res = $resource->reorder(['kb_2', 'kb_1']);

        $this->assertTrue($res->success);
        $this->assertCount(2, $res->sections);
        $this->assertSame('kb_2', $res->sections[0]->id);
    }

    public function testReplaceAll(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->stub('POST', '/v1/dashboard/knowledge', [
            'success' => true,
            'message' => 'All sections replaced',
            'data' => [
                'sections' => [
                    ['id' => 'kb_new1', 'position' => 0, 'title' => 'Sec 1', 'content' => 'C1', 'isOpen' => true],
                ],
                'total_sections' => 1,
            ],
        ]);

        $resource = new KnowledgeResource($mockHttp);
        $res = $resource->replaceAll([
            new CreateSectionRequest('Sec 1', 'C1', true),
        ]);

        $this->assertTrue($res->success);
        $this->assertSame(1, $res->totalSections);
        $this->assertCount(1, $res->sections);
    }

    public function testDelete(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->stub('DELETE', '/v1/dashboard/knowledge/kb_123', [
            'success' => true,
        ]);

        $resource = new KnowledgeResource($mockHttp);
        $this->assertTrue($resource->delete('kb_123'));
        $this->assertSame('/v1/dashboard/knowledge/kb_123', $mockHttp->recordedRequests[0]->path);
    }

    public function testBulkDelete(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->stub('DELETE', '/v1/dashboard/knowledge', [
            'success' => true,
        ]);

        $resource = new KnowledgeResource($mockHttp);
        $this->assertTrue($resource->bulkDelete(['kb_1', 'kb_2']));
        $this->assertSame(['ids' => ['kb_1', 'kb_2']], $mockHttp->recordedRequests[0]->body);
    }

    public function testBulkDeleteThrowsOnEmptyIds(): void
    {
        $resource = new KnowledgeResource(new MockHttpClient());
        $this->expectException(InvalidArgumentException::class);
        $resource->bulkDelete([]);
    }
}
