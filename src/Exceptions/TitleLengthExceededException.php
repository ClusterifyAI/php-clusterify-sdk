<?php

declare(strict_types=1);

namespace Clusterify\Exceptions;

use Throwable;

/**
 * Thrown when a knowledge section title exceeds the 100 character limit (HTTP 400: TITLE_LENGTH_EXCEEDED).
 */
class TitleLengthExceededException extends ClientException
{
    /**
     * @param string $message
     * @param array<string, mixed> $details
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message = 'Knowledge section title exceeds the maximum 100 characters limit.',
        array $details = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 'TITLE_LENGTH_EXCEEDED', 400, $details, $previous);
    }
}
