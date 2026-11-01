<?php

declare(strict_types=1);

namespace Clusterify\Exceptions;

use Throwable;

/**
 * Thrown when an account reaches its maximum allowed knowledge sections or registered URLs (HTTP 400: PLAN_LIMIT_EXCEEDED).
 */
class PlanLimitExceededException extends ClientException
{
    /**
     * @param string $message
     * @param array<string, mixed> $details
     * @param Throwable|null $previous
     * @param int $statusCode
     */
    public function __construct(
        string $message = 'Plan limit exceeded. You have reached the maximum number of items allowed on your plan.',
        array $details = [],
        ?Throwable $previous = null,
        int $statusCode = 400
    ) {
        parent::__construct($message, 'PLAN_LIMIT_EXCEEDED', $statusCode, $details, $previous);
    }
}
