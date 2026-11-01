<?php

declare(strict_types=1);

namespace Clusterify\Tests\Mocks;

use Clusterify\Exceptions\Factory\ExceptionFactory;
use Clusterify\Http\HttpClientInterface;
use Clusterify\Http\Request;
use Clusterify\Http\RequestOptions;
use Clusterify\Http\Response;
use Throwable;

final class MockHttpClient implements HttpClientInterface
{
    /** @var list<Request> */
    public array $recordedRequests = [];

    /** @var array<string, list<Response|Throwable>> */
    private array $stubs = [];

    /**
     * @param array<string, mixed> $payload
     */
    public function stub(string $method, string $path, array $payload, int $statusCode = 200): void
    {
        $key = strtoupper($method) . ':' . $path;
        $this->stubs[$key] = [new Response($statusCode, $payload)];
    }

    /**
     * Queue multiple sequential responses for the same endpoint (useful for pagination tests).
     *
     * @param array<string, mixed> $payload
     */
    public function queueStub(string $method, string $path, array $payload, int $statusCode = 200): void
    {
        $key = strtoupper($method) . ':' . $path;
        $this->stubs[$key][] = new Response($statusCode, $payload);
    }

    public function stubException(string $method, string $path, Throwable $e): void
    {
        $key = strtoupper($method) . ':' . $path;
        $this->stubs[$key] = [$e];
    }

    public function send(Request $request): Response
    {
        $this->recordedRequests[] = $request;
        $key = strtoupper($request->method) . ':' . $request->path;

        if (!empty($this->stubs[$key])) {
            $stub = count($this->stubs[$key]) > 1
                ? array_shift($this->stubs[$key])
                : $this->stubs[$key][0];

            if ($stub instanceof Throwable) {
                throw $stub;
            }
            if ($stub->statusCode >= 400) {
                throw ExceptionFactory::createFromResponse($stub->statusCode, $stub->payload);
            }
            return $stub;
        }

        return new Response(200, ['success' => true]);
    }

    /**
     * @param array<string, mixed> $queryParams
     */
    public function get(string $path, array $queryParams = [], ?RequestOptions $options = null): Response
    {
        return $this->send(new Request('GET', $path, $queryParams, null, [], $options));
    }

    /**
     * @param array<string, mixed>|null $body
     * @param array<string, mixed> $queryParams
     */
    public function post(string $path, ?array $body = null, array $queryParams = [], ?RequestOptions $options = null): Response
    {
        return $this->send(new Request('POST', $path, $queryParams, $body, [], $options));
    }

    /**
     * @param array<string, mixed>|null $body
     * @param array<string, mixed> $queryParams
     */
    public function patch(string $path, ?array $body = null, array $queryParams = [], ?RequestOptions $options = null): Response
    {
        return $this->send(new Request('PATCH', $path, $queryParams, $body, [], $options));
    }

    /**
     * @param array<string, mixed>|null $body
     * @param array<string, mixed> $queryParams
     */
    public function delete(string $path, ?array $body = null, array $queryParams = [], ?RequestOptions $options = null): Response
    {
        return $this->send(new Request('DELETE', $path, $queryParams, $body, [], $options));
    }
}
