<?php

declare(strict_types=1);

namespace Clusterify\Exceptions;

use Throwable;

/**
 * Base interface for all Clusterify SDK exceptions.
 */
interface ClusterifyExceptionInterface extends Throwable
{
    /**
     * Get the machine-readable error code if available (e.g. 'FORBIDDEN_PLAN').
     */
    public function getErrorCode(): ?string;

    /**
     * Get the HTTP status code if available (e.g. 403).
     */
    public function getStatusCode(): ?int;

    /**
     * Get additional structured error details provided by the API.
     *
     * @return array<string, mixed>
     */
    public function getDetails(): array;
}
