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
        $errorCode = isset($payload['error']) && is_string($payload['error'])
            ? $payload['error']
            : null;
        $message = isset($payload['message']) && is_string($payload['message'])
            ? $payload['message']
            : "HTTP error {$statusCode} received from Clusterify API.";

        $details = isset($payload['details']) && is_array($payload['details'])
            ? $payload['details']
            : [];

        // Match by specific error code first
        if ($errorCode !== null) {
            switch ($errorCode) {
                case 'MISSING_API_KEYS':
                case 'INVALID_API_KEYS':
                    return new AuthenticationException($message, $errorCode, $details);

                case 'API_KEY_DISABLED':
                case 'USER_DISABLED':
                case 'USER_NOT_VERIFIED':
                    return new AuthorizationException($message, $errorCode, $details);

                case 'FORBIDDEN_PLAN':
                    return new ForbiddenPlanException($message, $details);

                case 'PLAN_LIMIT_EXCEEDED':
                    return new PlanLimitExceededException($message, $details);

                case 'CONTENT_LENGTH_EXCEEDED':
                    return new ContentLengthExceededException($message, $details);

                case 'TITLE_LENGTH_EXCEEDED':
                    return new TitleLengthExceededException($message, $details);

                case 'DOMAIN_MISMATCH':
                    return new DomainMismatchException($message, $details);

                case 'DUPLICATE_URL':
                    return new DuplicateUrlException($message, $details);

                case 'NO_CHATBOT_CONFIGURED':
                    return new NoChatbotConfiguredException($message, $details);

                case 'NOT_FOUND':
                    return new ResourceNotFoundException($message, $details);

                case 'VALIDATION_ERROR':
                    return new ValidationException($message, $details);

                case 'RATE_LIMIT_EXCEEDED':
                    $retryAfter = $response !== null ? RetryPolicy::parseRetryAfter($response) : null;
                    return new RateLimitExceededException($message, $retryAfter, $details);
            }
        }

        // Match by HTTP status code
        if ($statusCode === 429) {
            $retryAfter = $response !== null ? RetryPolicy::parseRetryAfter($response) : null;
            return new RateLimitExceededException($message, $retryAfter, $details);
        }

        if ($statusCode === 401) {
            return new AuthenticationException($message, $errorCode ?? 'UNAUTHORIZED', $details);
        }

        if ($statusCode === 403) {
            return new AuthorizationException($message, $errorCode ?? 'FORBIDDEN', $details);
        }

        if ($statusCode === 404) {
            return new ResourceNotFoundException($message, $details);
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
