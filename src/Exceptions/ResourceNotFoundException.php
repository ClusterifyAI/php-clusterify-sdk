<?php

declare(strict_types=1);

namespace Clusterify\Exceptions;

use Throwable;

/**
 * Thrown when a specific section ID or URL knowledge entry is not found (HTTP 404: NOT_FOUND).
 */
class ResourceNotFoundException extends ClientException
{
    /**
     * @param string $message
     * @param array<string, mixed> $details
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message = 'The requested resource was not found.',
        array $details = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 'NOT_FOUND', 404, $details, $previous);
    }
}
