<?php

declare(strict_types=1);

namespace Clusterify\Tests\Unit\Resources;

use Clusterify\Resources\PingResource;
use Clusterify\Tests\Mocks\MockHttpClient;
use PHPUnit\Framework\TestCase;

final class PingResourceTest extends TestCase
{
    public function testPingSuccess(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->stub('GET', '/v1/dashboard/ping', [
            'success' => true,
            'status' => 'healthy',
            'authenticated' => true,
            'timestamp' => '2026-08-31T21:15:00.000Z',
        ]);

        $resource = new PingResource($mockHttp);
        $res = $resource->ping();

        $this->assertTrue($res->success);
        $this->assertSame('healthy', $res->status);
        $this->assertTrue($res->authenticated);
        $this->assertSame('2026-08-31T21:15:00.000Z', $res->timestamp);
        $this->assertCount(1, $mockHttp->recordedRequests);
        $this->assertSame('GET', $mockHttp->recordedRequests[0]->method);
        $this->assertSame('/v1/dashboard/ping', $mockHttp->recordedRequests[0]->path);
    }
}
