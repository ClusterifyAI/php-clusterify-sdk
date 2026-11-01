<?php

declare(strict_types=1);

namespace Clusterify\Exceptions;

use Throwable;

/**
 * Thrown when an account does not have a chatbot configured yet (HTTP 400: NO_CHATBOT_CONFIGURED).
 */
class NoChatbotConfiguredException extends ClientException
{
    /**
     * @param string $message
     * @param array<string, mixed> $details
     * @param Throwable|null $previous
     * @param int $statusCode
     */
    public function __construct(
        string $message = 'No chatbot configured for this account. Please set up a chatbot domain in the dashboard.',
        array $details = [],
        ?Throwable $previous = null,
        int $statusCode = 400
    ) {
        parent::__construct($message, 'NO_CHATBOT_CONFIGURED', $statusCode, $details, $previous);
    }
}
