<?php

declare(strict_types=1);

namespace Clusterify\Exceptions;

use Throwable;

/**
 * Thrown when access is forbidden due to account state (HTTP 403: API_KEY_DISABLED, USER_DISABLED, USER_NOT_VERIFIED).
 */
class AuthorizationException extends ClientException
{
    /**
     * @param string $message
     * @param string $errorCode
     * @param array<string, mixed> $details
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message = 'Access forbidden. Please check your account status.',
        string $errorCode = 'API_KEY_DISABLED',
        array $details = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $errorCode, 403, $details, $previous);
    }
}
