<?php

declare(strict_types=1);

namespace Clusterify\Resources;

use Clusterify\DTO\Ping\PingResponse;
use Clusterify\Exceptions\ClusterifyException;
use Clusterify\Http\RequestOptions;

/**
 * Healthcheck & authentication ping resource.
 */
final class PingResource extends AbstractResource
{
    /**
     * Perform a fast healthcheck and verify that the configured API keys are authenticated.
     *
     * @param RequestOptions|null $options
     * @return PingResponse
     * @throws ClusterifyException
     */
    public function ping(?RequestOptions $options = null): PingResponse
    {
        $response = $this->http->get('/v1/dashboard/ping', [], $options);
        return PingResponse::fromArray($response->payload);
    }
}
