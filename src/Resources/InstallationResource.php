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
     * @param RequestOptions|null $options Per-request options (e.g. RequestOptions::noCache())
     * @param string|null $hostSystem Optional CMS/host platform identifier (e.g. 'magento2', 'wordpress')
     * @return InstallationResponse
     * @throws ClusterifyException
     */
    public function get(?RequestOptions $options = null, ?string $hostSystem = null): InstallationResponse
    {
        $queryParams = [];
        if ($hostSystem !== null && trim($hostSystem) !== '') {
            $queryParams['host_system'] = trim($hostSystem);
        }

        $response = $this->http->get('/v1/dashboard/installation', $queryParams, $options);
        return InstallationResponse::fromArray($response->payload);
    }
}
