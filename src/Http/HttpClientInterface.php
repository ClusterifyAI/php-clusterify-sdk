<?php

declare(strict_types=1);

namespace Clusterify\Http;

use Clusterify\Exceptions\ClusterifyException;

/**
 * Internal HTTP client contract for Clusterify API communication.
 */
interface HttpClientInterface
{
    /**
     * Send an HTTP request and return the parsed Response.
     *
     * @param Request $request
     * @return Response
     * @throws ClusterifyException
     */
    public function send(Request $request): Response;

    /**
     * Perform a GET request.
     *
     * @param string $path URL path (e.g. '/v1/dashboard/knowledge')
     * @param array<string, mixed> $queryParams Query parameters
     * @param RequestOptions|null $options
     * @return Response
     * @throws ClusterifyException
     */
    public function get(string $path, array $queryParams = [], ?RequestOptions $options = null): Response;

    /**
     * Perform a POST request.
     *
     * @param string $path URL path
     * @param array<string, mixed>|null $body Request JSON payload
     * @param array<string, mixed> $queryParams Query parameters
     * @param RequestOptions|null $options
     * @return Response
     * @throws ClusterifyException
     */
    public function post(string $path, ?array $body = null, array $queryParams = [], ?RequestOptions $options = null): Response;

    /**
     * Perform a PATCH request.
     *
     * @param string $path URL path
     * @param array<string, mixed>|null $body Request JSON payload
     * @param array<string, mixed> $queryParams Query parameters
     * @param RequestOptions|null $options
     * @return Response
     * @throws ClusterifyException
     */
    public function patch(string $path, ?array $body = null, array $queryParams = [], ?RequestOptions $options = null): Response;

    /**
     * Perform a DELETE request.
     *
     * @param string $path URL path
     * @param array<string, mixed>|null $body Request JSON payload
     * @param array<string, mixed> $queryParams Query parameters
     * @param RequestOptions|null $options
     * @return Response
     * @throws ClusterifyException
     */
    public function delete(string $path, ?array $body = null, array $queryParams = [], ?RequestOptions $options = null): Response;
}
