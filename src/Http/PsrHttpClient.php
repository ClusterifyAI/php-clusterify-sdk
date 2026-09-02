<?php

declare(strict_types=1);

namespace Clusterify\Http;

use Clusterify\Configuration;
use Clusterify\Exceptions\ClusterifyException;
use Clusterify\Exceptions\Factory\ExceptionFactory;
use Clusterify\Exceptions\NetworkException;
use Clusterify\Http\Middleware\MiddlewareInterface;
use Clusterify\Http\Serializer\JsonSerializer;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Throwable;

/**
 * PSR-18 compliant HTTP transport client for the Clusterify API.
 */
final class PsrHttpClient implements HttpClientInterface
{
    private ClientInterface $psrClient;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;

    /**
     * @var list<MiddlewareInterface>
     */
    private array $middlewares = [];

    /**
     * @param Configuration $configuration Client configuration
     * @param ClientInterface|null $psrClient PSR-18 HTTP client (auto-discovered if null)
     * @param RequestFactoryInterface|null $requestFactory PSR-17 Request Factory (auto-discovered if null)
     * @param StreamFactoryInterface|null $streamFactory PSR-17 Stream Factory (auto-discovered if null)
     * @param list<MiddlewareInterface> $middlewares Middleware stack
     */
    public function __construct(
        private readonly Configuration $configuration,
        ?ClientInterface $psrClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        array $middlewares = []
    ) {
        $this->psrClient = $psrClient ?? Psr18ClientDiscovery::find();
        $this->requestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
        $this->middlewares = $middlewares;
    }

    /**
     * Add a middleware to the execution stack.
     */
    public function addMiddleware(MiddlewareInterface $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $path, array $queryParams = [], ?RequestOptions $options = null): Response
    {
        return $this->send(new Request('GET', $path, $queryParams, null, [], $options));
    }

    /**
     * {@inheritdoc}
     */
    public function post(string $path, ?array $body = null, array $queryParams = [], ?RequestOptions $options = null): Response
    {
        return $this->send(new Request('POST', $path, $queryParams, $body, [], $options));
    }

    /**
     * {@inheritdoc}
     */
    public function patch(string $path, ?array $body = null, array $queryParams = [], ?RequestOptions $options = null): Response
    {
        return $this->send(new Request('PATCH', $path, $queryParams, $body, [], $options));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $path, ?array $body = null, array $queryParams = [], ?RequestOptions $options = null): Response
    {
        return $this->send(new Request('DELETE', $path, $queryParams, $body, [], $options));
    }

    /**
     * {@inheritdoc}
     */
    public function send(Request $request): Response
    {
        $pipeline = $this->buildPipeline();
        return $pipeline($request);
    }

    /**
     * Build the composed middleware pipeline terminating at the PSR-18 HTTP sender.
     *
     * @return callable(Request): Response
     */
    private function buildPipeline(): callable
    {
        $coreSender = fn(Request $req): Response => $this->executeHttpRequest($req);

        return array_reduce(
            array_reverse($this->middlewares),
            static fn(callable $next, MiddlewareInterface $middleware): callable =>
                static fn(Request $request): Response => $middleware->handle($request, $next),
            $coreSender
        );
    }

    /**
     * Execute the raw PSR-7 request and process the PSR-7 response.
     *
     * @throws ClusterifyException
     */
    private function executeHttpRequest(Request $request): Response
    {
        // 1. Build Query Parameters
        $queryParams = $request->queryParams;
        if ($request->options?->noCache === true) {
            $queryParams['nocache'] = '1';
        }

        // 2. Build URI
        $url = $this->configuration->getCleanBaseUrl() . '/' . ltrim($request->path, '/');
        if (!empty($queryParams)) {
            $queryString = http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);
            $url .= (str_contains($url, '?') ? '&' : '?') . $queryString;
        }

        // 3. Create PSR-7 Request
        $psrRequest = $this->requestFactory->createRequest($request->method, $url);

        // 4. Attach Default & Auth Headers
        $headers = array_merge(
            [
                'Accept' => 'application/json',
                'User-Agent' => $this->configuration->userAgent,
            ],
            $this->configuration->defaultHeaders,
            $this->configuration->credentials->toHeaders(),
            $request->headers,
            $request->options?->headers ?? []
        );

        if ($request->options?->noCache === true) {
            $headers['x-no-cache'] = 'true';
        }

        if ($request->body !== null) {
            $headers['Content-Type'] = 'application/json';
            $bodyContent = JsonSerializer::encode($request->body);
            $psrRequest = $psrRequest->withBody($this->streamFactory->createStream($bodyContent));
        }

        foreach ($headers as $name => $value) {
            $psrRequest = $psrRequest->withHeader($name, $value);
        }

        // 5. Send via PSR-18 Client
        try {
            $psrResponse = $this->psrClient->sendRequest($psrRequest);
        } catch (ClientExceptionInterface $e) {
            throw new NetworkException('HTTP request failed: ' . $e->getMessage(), $e);
        } catch (Throwable $e) {
            throw new NetworkException('Unexpected connection failure: ' . $e->getMessage(), $e);
        }

        // 6. Handle & Parse Response
        return $this->handlePsrResponse($psrResponse);
    }

    /**
     * Parse PSR-7 response and evaluate status codes.
     *
     * @throws ClusterifyException
     */
    private function handlePsrResponse(ResponseInterface $psrResponse): Response
    {
        $statusCode = $psrResponse->getStatusCode();
        $bodyString = (string) $psrResponse->getBody();
        $payload = JsonSerializer::decode($bodyString);

        if ($statusCode >= 400) {
            throw ExceptionFactory::createFromResponse($statusCode, $payload, $psrResponse);
        }

        return new Response(
            statusCode: $statusCode,
            payload: $payload,
            headers: $psrResponse->getHeaders(),
            psrResponse: $psrResponse
        );
    }
}
