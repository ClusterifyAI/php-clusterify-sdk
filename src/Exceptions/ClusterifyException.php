<?php

declare(strict_types=1);

namespace Clusterify\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Base runtime exception for the Clusterify SDK.
 */
class ClusterifyException extends RuntimeException implements ClusterifyExceptionInterface
{
    /**
     * @param string $message Exception message
     * @param string|null $errorCode Machine-readable error code (e.g. 'VALIDATION_ERROR')
     * @param int|null $statusCode HTTP status code (e.g. 400)
     * @param array<string, mixed> $details Structured error details from the API response
     * @param Throwable|null $previous Previous exception in chain
     */
    public function __construct(
        string $message = '',
        protected readonly ?string $errorCode = null,
        protected readonly ?int $statusCode = null,
        protected readonly array $details = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode ?? 0, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getDetails(): array
    {
        return $this->details;
    }
}
