<?php

declare(strict_types=1);

namespace Clusterify\Tests\Unit\Resources;

use Clusterify\DTO\KnowledgeUrl\KnowledgeUrlQuery;
use Clusterify\DTO\KnowledgeUrl\UpsertUrlRequest;
use Clusterify\Exceptions\ResourceNotFoundException;
use Clusterify\Resources\KnowledgeUrlResource;
use Clusterify\Tests\Mocks\MockHttpClient;
use PHPUnit\Framework\TestCase;

final class KnowledgeUrlResourceTest extends TestCase
{
    public function testList(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->stub('GET', '/v1/dashboard/knowledge-url', [
            'success' => true,
            'data' => [
                'items' => [
                    [
                        'id' => 42,
                        'user_id' => 101,
                        'chatbot_id' => 50,
                        'url' => 'https://example.com/pricing',
                        'is_enabled' => true,
                        'content' => '# Pricing\n- Pro: $37/mo',
                        'has_embedding' => true,
                        'embedding_at' => '2026-03-12T14:30:00.000Z',
                        'created_at' => '2026-03-01T10:00:00.000Z',
                        'modified_at' => '2026-03-12T14:30:00.000Z',
                    ],
                ],
                'pagination' => [
                    'page' => 1,
                    'limit' => 10,
                    'total_items' => 1,
                    'total_pages' => 1,
                ],
                'stats' => [
                    'total' => 1,
                    'enabled' => 1,
                    'disabled' => 0,
                    'max_urls' => 1000,
                    'max_content_length' => 20000,
                ],
                'chatbot' => [
                    'id' => 50,
                    'domain' => 'https://example.com',
                ],
            ],
        ]);

        $resource = new KnowledgeUrlResource($mockHttp);
        $query = KnowledgeUrlQuery::create()->setPage(1)->setLimit(10)->setStatus('enabled');
        $res = $resource->list($query);

        $this->assertTrue($res->success);
        $this->assertCount(1, $res->items);
        $this->assertSame('https://example.com/pricing', $res->items[0]->url);
        $this->assertSame(1, $res->pagination->page);
        $this->assertSame(1, $res->stats->total);
        $this->assertSame('https://example.com', $res->chatbot->domain);
    }

    public function testGetById(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->stub('GET', '/v1/dashboard/knowledge-url', [
            'success' => true,
            'data' => [
                'items' => [
                    [
                        'id' => 42,
                        'user_id' => 101,
                        'chatbot_id' => 50,
                        'url' => 'https://example.com/pricing',
                        'is_enabled' => true,
                        'content' => '# Pricing',
                        'has_embedding' => false,
                    ],
                ],
            ],
        ]);

        $resource = new KnowledgeUrlResource($mockHttp);
        $item = $resource->get(42);

        $this->assertSame(42, $item->id);
        $this->assertSame('https://example.com/pricing', $item->url);
        $this->assertSame(['id' => 42], $mockHttp->recordedRequests[0]->queryParams);
    }

    public function testGetByUrl(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->stub('GET', '/v1/dashboard/knowledge-url', [
            'success' => true,
            'data' => [
                'items' => [
                    [
                        'id' => 42,
                        'url' => 'https://example.com/pricing',
                        'content' => '# Pricing',
                        'is_enabled' => true,
                        'has_embedding' => true,
                    ],
                ],
            ],
        ]);

        $resource = new KnowledgeUrlResource($mockHttp);
        $item = $resource->getByUrl('https://example.com/pricing');

        $this->assertSame(42, $item->id);
        $this->assertSame(['url' => 'https://example.com/pricing'], $mockHttp->recordedRequests[0]->queryParams);
    }

    public function testGetNotFoundThrows(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->stub('GET', '/v1/dashboard/knowledge-url', [
            'success' => true,
            'data' => [
                'items' => [],
            ],
        ]);

        $resource = new KnowledgeUrlResource($mockHttp);
        $this->expectException(ResourceNotFoundException::class);
        $resource->get(999);
    }

    public function testStats(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->stub('GET', '/v1/dashboard/knowledge-url', [
            'success' => true,
            'data' => [
                'stats' => [
                    'total' => 15,
                    'enabled' => 12,
                    'disabled' => 3,
                    'max_urls' => 1000,
                    'max_content_length' => 20000,
                ],
            ],
        ]);

        $resource = new KnowledgeUrlResource($mockHttp);
        $stats = $resource->stats();

        $this->assertSame(15, $stats->total);
        $this->assertSame(12, $stats->enabled);
        $this->assertSame(3, $stats->disabled);
    }

    public function testUpsert(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->stub('POST', '/v1/dashboard/knowledge-url', [
            'success' => true,
            'data' => [
                'item' => [
                    'id' => 42,
                    'url' => 'https://example.com/pricing',
                    'content' => '# Pricing',
                    'is_enabled' => true,
                    'has_embedding' => false,
                ],
            ],
        ]);

        $resource = new KnowledgeUrlResource($mockHttp);
        $item = $resource->upsert('https://example.com/pricing', '# Pricing');

        $this->assertSame(42, $item->id);
        $this->assertSame('https://example.com/pricing', $item->url);
    }

    public function testBatchUpsert(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->stub('POST', '/v1/dashboard/knowledge-url', [
            'success' => true,
            'data' => [
                'processed' => 2,
                'errors_count' => 0,
                'results' => [
                    [
                        'action' => 'created',
                        'item' => ['id' => 1, 'url' => 'https://example.com/1', 'content' => '1', 'is_enabled' => true, 'has_embedding' => false],
                    ],
                    [
                        'action' => 'updated',
                        'item' => ['id' => 2, 'url' => 'https://example.com/2', 'content' => '2', 'is_enabled' => true, 'has_embedding' => false],
                    ],
                ],
                'errors' => [],
            ],
        ]);

        $resource = new KnowledgeUrlResource($mockHttp);
        $batch = $resource->batchUpsert([
            ['url' => 'https://example.com/1', 'content' => '1'],
            ['url' => 'https://example.com/2', 'content' => '2'],
        ]);

        $this->assertTrue($batch->success);
        $this->assertSame(2, $batch->processed);
        $this->assertSame(0, $batch->errorsCount);
        $this->assertCount(2, $batch->results);
        $this->assertSame('created', $batch->results[0]->action);
        $this->assertSame('updated', $batch->results[1]->action);
    }

    public function testAutoPaginate(): void
    {
        $mockHttp = new MockHttpClient();

        // Page 1
        $mockHttp->stub('GET', '/v1/dashboard/knowledge-url', [
            'success' => true,
            'data' => [
                'items' => [
                    ['id' => 1, 'url' => 'https://example.com/1', 'content' => '1', 'is_enabled' => true, 'has_embedding' => false],
                ],
                'pagination' => ['page' => 1, 'limit' => 1, 'total_items' => 2, 'total_pages' => 2],
            ],
        ]);

        $resource = new KnowledgeUrlResource($mockHttp);
        $generator = $resource->autoPaginate(['limit' => 1]);

        $items = [];
        foreach ($generator as $item) {
            $items[] = $item;
            break; // Stop after first to test generator yield
        }

        $this->assertCount(1, $items);
        $this->assertSame(1, $items[0]->id);
    }

    public function testDelete(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->stub('DELETE', '/v1/dashboard/knowledge-url/42', [
            'success' => true,
        ]);

        $resource = new KnowledgeUrlResource($mockHttp);
        $this->assertTrue($resource->delete(42));
    }

    public function testBulkDelete(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->stub('DELETE', '/v1/dashboard/knowledge-url', [
            'success' => true,
        ]);

        $resource = new KnowledgeUrlResource($mockHttp);
        $this->assertTrue($resource->bulkDelete([42], ['https://example.com/old']));
        $this->assertSame([
            'ids' => [42],
            'urls' => ['https://example.com/old'],
        ], $mockHttp->recordedRequests[0]->body);
    }
}
