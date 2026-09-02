<?php

declare(strict_types=1);

namespace Clusterify\Resources;

use Clusterify\DTO\Installation\InstallationResponse;
use Clusterify\Exceptions\ClusterifyException;
use Clusterify\Http\RequestOptions;

/**
 * Chatbot installation snippet and platform guides resource.
 */
final class InstallationResource extends AbstractResource
{
    /**
     * Retrieve the embeddable HTML script tag snippet and comprehensive integration guides.
     *
     * @param RequestOptions|null $options
     * @return InstallationResponse
     * @throws ClusterifyException
     */
    public function get(?RequestOptions $options = null): InstallationResponse
    {
        $response = $this->http->get('/v1/dashboard/installation', [], $options);
        return InstallationResponse::fromArray($response->payload);
    }
}
