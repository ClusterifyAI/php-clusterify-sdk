<?php

declare(strict_types=1);

namespace Clusterify\Exceptions;

use Throwable;

/**
 * Thrown when request payload fails validation (HTTP 400: VALIDATION_ERROR).
 */
class ValidationException extends ClientException
{
    /**
     * @var array<string|int, mixed>
     */
    protected readonly array $errors;

    /**
     * @param string $message
     * @param array<string, mixed> $details
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message = 'Validation failed for request payload.',
        array $details = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 'VALIDATION_ERROR', 400, $details, $previous);

        $this->errors = isset($details['errors']) && is_array($details['errors'])
            ? $details['errors']
            : $details;
    }

    /**
     * Get validation error details if returned by the API.
     *
     * @return array<string|int, mixed>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
