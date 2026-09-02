<?php

declare(strict_types=1);

namespace Clusterify\Resources;

use Clusterify\DTO\Profile\ProfileResponse;
use Clusterify\Exceptions\ClusterifyException;
use Clusterify\Http\RequestOptions;

/**
 * Tenant profile and subscription plan resource.
 */
final class ProfileResource extends AbstractResource
{
    /**
     * Retrieve the tenant profile information and subscription plan details.
     *
     * @param RequestOptions|null $options
     * @return ProfileResponse
     * @throws ClusterifyException
     */
    public function get(?RequestOptions $options = null): ProfileResponse
    {
        $response = $this->http->get('/v1/dashboard/profile', [], $options);
        return ProfileResponse::fromArray($response->payload);
    }
}
