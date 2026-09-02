<?php

declare(strict_types=1);

namespace Clusterify\Resources;

use Clusterify\Http\HttpClientInterface;

/**
 * Base class for all API resource repositories.
 */
abstract class AbstractResource
{
    /**
     * @param HttpClientInterface $http
     */
    public function __construct(
        protected readonly HttpClientInterface $http
    ) {}
}
