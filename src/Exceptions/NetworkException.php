<?php

declare(strict_types=1);

namespace Clusterify\Exceptions;

use Throwable;

/**
 * Thrown when a network-level communication failure occurs (DNS lookup failure, connection timeout, socket error).
 */
class NetworkException extends ClusterifyException
{
    /**
     * @param string $message
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message = 'Network connection failed',
        ?Throwable $previous = null
    ) {
        parent::__construct(
            message: $message,
            errorCode: 'NETWORK_ERROR',
            statusCode: null,
            details: [],
            previous: $previous
        );
    }
}
