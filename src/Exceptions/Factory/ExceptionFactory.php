<?php

declare(strict_types=1);

namespace Clusterify\Exceptions\Factory;

use Clusterify\Exceptions\AuthenticationException;
use Clusterify\Exceptions\AuthorizationException;
use Clusterify\Exceptions\ClientException;
use Clusterify\Exceptions\ClusterifyException;
use Clusterify\Exceptions\ContentLengthExceededException;
use Clusterify\Exceptions\DomainMismatchException;
use Clusterify\Exceptions\DuplicateUrlException;
use Clusterify\Exceptions\ForbiddenPlanException;
use Clusterify\Exceptions\NoChatbotConfiguredException;
use Clusterify\Exceptions\PlanLimitExceededException;
use Clusterify\Exceptions\RateLimitExceededException;
use Clusterify\Exceptions\ResourceNotFoundException;
use Clusterify\Exceptions\ServerException;
use Clusterify\Exceptions\TitleLengthExceededException;
use Clusterify\Exceptions\ValidationException;
use Clusterify\Support\Payload;
use Clusterify\Support\RetryPolicy;
use Psr\Http\Message\ResponseInterface;

/**
 * Factory for creating domain-specific typed exceptions from API error envelopes.
 */
final class ExceptionFactory
{
    /**
     * Create a typed exception from HTTP status and response payload.
     *
     * @param int $statusCode
     * @param array<string, mixed>|null $payload
     * @param ResponseInterface|null $response
     * @return ClusterifyException
     */
    public static function createFromResponse(
        int $statusCode,
        ?array $payload = null,
        ?ResponseInterface $response = null
    ): ClusterifyException {
        $payload ??= [];
        $errorCode = Payload::nullableString($payload, 'error');
        $message = Payload::nullableString($payload, 'message')
            ?? "HTTP error {$statusCode} received from Clusterify API.";
        $details = Payload::map($payload, 'details');

        // Match by specific error code first
        if ($errorCode !== null) {
            switch ($errorCode) {
                case 'MISSING_API_KEYS':
                case 'INVALID_API_KEYS':
                    return new AuthenticationException($message, $errorCode, $details, null, $statusCode);

                case 'API_KEY_DISABLED':
                case 'USER_DISABLED':
                case 'USER_NOT_VERIFIED':
                    return new AuthorizationException($message, $errorCode, $details, null, $statusCode);

                case 'FORBIDDEN_PLAN':
                    return new ForbiddenPlanException($message, $details, null, $statusCode);

                case 'PLAN_LIMIT_EXCEEDED':
                    return new PlanLimitExceededException($message, $details, null, $statusCode);

                case 'CONTENT_LENGTH_EXCEEDED':
                    return new ContentLengthExceededException($message, $details, null, $statusCode);

                case 'TITLE_LENGTH_EXCEEDED':
                    return new TitleLengthExceededException($message, $details, null, $statusCode);

                case 'DOMAIN_MISMATCH':
                    return new DomainMismatchException($message, $details, null, $statusCode);

                case 'DUPLICATE_URL':
                    return new DuplicateUrlException($message, $details, null, $statusCode);

                case 'NO_CHATBOT_CONFIGURED':
                    return new NoChatbotConfiguredException($message, $details, null, $statusCode);

                case 'NOT_FOUND':
                    return new ResourceNotFoundException($message, $details, null, $statusCode);

                case 'VALIDATION_ERROR':
                    return new ValidationException($message, $details, null, $statusCode);

                case 'RATE_LIMIT_EXCEEDED':
                    $retryAfter = ($response !== null ? RetryPolicy::parseRetryAfter($response) : null)
                        ?? Payload::nullableInt($details, 'retry_after');
                    return new RateLimitExceededException($message, $retryAfter, $details, null, $statusCode);
            }
        }

        // Match by HTTP status code
        if ($statusCode === 429) {
            $retryAfter = ($response !== null ? RetryPolicy::parseRetryAfter($response) : null)
                ?? Payload::nullableInt($details, 'retry_after');
            return new RateLimitExceededException($message, $retryAfter, $details, null, $statusCode);
        }

        if ($statusCode === 401) {
            return new AuthenticationException($message, $errorCode ?? 'UNAUTHORIZED', $details, null, $statusCode);
        }

        if ($statusCode === 403) {
            return new AuthorizationException($message, $errorCode ?? 'FORBIDDEN', $details, null, $statusCode);
        }

        if ($statusCode === 404) {
            return new ResourceNotFoundException($message, $details, null, $statusCode);
        }

        if ($statusCode >= 500) {
            return new ServerException($message, $errorCode ?? 'SERVER_ERROR', $statusCode, $details);
        }

        if ($statusCode >= 400) {
            return new ClientException($message, $errorCode ?? 'CLIENT_ERROR', $statusCode, $details);
        }

        return new ClusterifyException($message, $errorCode, $statusCode, $details);
    }
}
