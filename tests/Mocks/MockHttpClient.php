<?php

declare(strict_types=1);

namespace Clusterify\Tests\Mocks;

use Clusterify\Exceptions\Factory\ExceptionFactory;
use Clusterify\Http\HttpClientInterface;
use Clusterify\Http\Request;
use Clusterify\Http\RequestOptions;
use Clusterify\Http\Response;

final class MockHttpClient implements HttpClientInterface
{
    /** @var list<Request> */
    public array $recordedRequests = [];

    /** @var array<string, Response|\Throwable> */
    private array $stubs = [];

    public function stub(string $method, string $path, array $payload, int $statusCode = 200): void
    {
        $key = strtoupper($method) . ':' . $path;
        $this->stubs[$key] = new Response($statusCode, $payload);
    }

    public function stubException(string $method, string $path, \Throwable $e): void
    {
        $key = strtoupper($method) . ':' . $path;
        $this->stubs[$key] = $e;
    }

    public function send(Request $request): Response
    {
        $this->recordedRequests[] = $request;
        $key = strtoupper($request->method) . ':' . $request->path;

        if (isset($this->stubs[$key])) {
            $stub = $this->stubs[$key];
            if ($stub instanceof \Throwable) {
                throw $stub;
            }
            if ($stub->statusCode >= 400) {
                throw ExceptionFactory::createFromResponse($stub->statusCode, $stub->payload);
            }
            return $stub;
        }

        return new Response(200, ['success' => true]);
    }

    public function get(string $path, array $queryParams = [], ?RequestOptions $options = null): Response
    {
        return $this->send(new Request('GET', $path, $queryParams, null, [], $options));
    }

    public function post(string $path, ?array $body = null, array $queryParams = [], ?RequestOptions $options = null): Response
    {
        return $this->send(new Request('POST', $path, $queryParams, $body, [], $options));
    }

    public function patch(string $path, ?array $body = null, array $queryParams = [], ?RequestOptions $options = null): Response
    {
        return $this->send(new Request('PATCH', $path, $queryParams, $body, [], $options));
    }

    public function delete(string $path, ?array $body = null, array $queryParams = [], ?RequestOptions $options = null): Response
    {
        return $this->send(new Request('DELETE', $path, $queryParams, $body, [], $options));
    }
}
