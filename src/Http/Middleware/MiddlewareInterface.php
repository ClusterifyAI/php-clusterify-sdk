<?php

declare(strict_types=1);

namespace Clusterify\Http\Middleware;

use Clusterify\Http\Request;
use Clusterify\Http\Response;

/**
 * Middleware contract for intercepting and transforming requests and responses.
 */
interface MiddlewareInterface
{
    /**
     * Process an outgoing request and return a response.
     *
     * @param Request $request
     * @param callable(Request): Response $next
     * @return Response
     */
    public function handle(Request $request, callable $next): Response;
}
