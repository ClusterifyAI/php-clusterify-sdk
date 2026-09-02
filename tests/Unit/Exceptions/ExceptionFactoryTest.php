<?php

declare(strict_types=1);

namespace Clusterify\Tests\Unit\Exceptions;

use Clusterify\Exceptions\AuthenticationException;
use Clusterify\Exceptions\AuthorizationException;
use Clusterify\Exceptions\ClientException;
use Clusterify\Exceptions\ContentLengthExceededException;
use Clusterify\Exceptions\DomainMismatchException;
use Clusterify\Exceptions\DuplicateUrlException;
use Clusterify\Exceptions\Factory\ExceptionFactory;
use Clusterify\Exceptions\ForbiddenPlanException;
use Clusterify\Exceptions\NoChatbotConfiguredException;
use Clusterify\Exceptions\PlanLimitExceededException;
use Clusterify\Exceptions\RateLimitExceededException;
use Clusterify\Exceptions\ResourceNotFoundException;
use Clusterify\Exceptions\ServerException;
use Clusterify\Exceptions\TitleLengthExceededException;
use Clusterify\Exceptions\ValidationException;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use PHPUnit\Framework\TestCase;

final class ExceptionFactoryTest extends TestCase
{
    public function testCreatesForbiddenPlanException(): void
    {
        $payload = [
            'success' => false,
            'error' => 'FORBIDDEN_PLAN',
            'message' => 'URL-based knowledge requires Professional plan.',
            'status_code' => 403,
            'details' => [
                'current_plan' => 1,
                'required_plan' => 2,
                'upgrade_url' => 'https://dashboard.clusterify.ai/billing',
                'billing_page' => '/billing',
            ],
        ];

        $e = ExceptionFactory::createFromResponse(403, $payload);
        $this->assertInstanceOf(ForbiddenPlanException::class, $e);
        $this->assertSame('FORBIDDEN_PLAN', $e->getErrorCode());
        $this->assertSame(403, $e->getStatusCode());
        $this->assertSame(1, $e->getCurrentPlan());
        $this->assertSame(2, $e->getRequiredPlan());
        $this->assertSame('https://dashboard.clusterify.ai/billing', $e->getUpgradeUrl());
        $this->assertSame('/billing', $e->getBillingPage());
    }

    public function testCreatesAuthenticationException(): void
    {
        $payload = ['error' => 'MISSING_API_KEYS', 'message' => 'Missing keys'];
        $e = ExceptionFactory::createFromResponse(401, $payload);
        $this->assertInstanceOf(AuthenticationException::class, $e);
        $this->assertSame('MISSING_API_KEYS', $e->getErrorCode());

        $payload2 = ['error' => 'INVALID_API_KEYS', 'message' => 'Invalid keys'];
        $e2 = ExceptionFactory::createFromResponse(401, $payload2);
        $this->assertInstanceOf(AuthenticationException::class, $e2);
    }

    public function testCreatesAuthorizationException(): void
    {
        $payload = ['error' => 'API_KEY_DISABLED', 'message' => 'Disabled'];
        $e = ExceptionFactory::createFromResponse(403, $payload);
        $this->assertInstanceOf(AuthorizationException::class, $e);
        $this->assertSame('API_KEY_DISABLED', $e->getErrorCode());
    }

    public function testCreatesValidationExceptionWithErrors(): void
    {
        $payload = [
            'error' => 'VALIDATION_ERROR',
            'message' => 'Invalid input',
            'details' => [
                'errors' => [
                    ['field' => 'title', 'message' => 'Title is required'],
                ],
            ],
        ];
        $e = ExceptionFactory::createFromResponse(400, $payload);
        $this->assertInstanceOf(ValidationException::class, $e);
        $this->assertCount(1, $e->getErrors());
    }

    public function testCreatesRateLimitExceededExceptionWithHeader(): void
    {
        $psrResponse = new GuzzleResponse(429, ['Retry-After' => '120'], '{"error":"RATE_LIMIT_EXCEEDED"}');
        $payload = ['error' => 'RATE_LIMIT_EXCEEDED', 'message' => 'Too many requests'];

        $e = ExceptionFactory::createFromResponse(429, $payload, $psrResponse);
        $this->assertInstanceOf(RateLimitExceededException::class, $e);
        $this->assertSame(120, $e->getRetryAfter());
    }

    public function testCreatesPlanLimitExceededException(): void
    {
        $payload = ['error' => 'PLAN_LIMIT_EXCEEDED'];
        $e = ExceptionFactory::createFromResponse(400, $payload);
        $this->assertInstanceOf(PlanLimitExceededException::class, $e);
    }

    public function testCreatesContentLengthExceededException(): void
    {
        $payload = ['error' => 'CONTENT_LENGTH_EXCEEDED'];
        $e = ExceptionFactory::createFromResponse(400, $payload);
        $this->assertInstanceOf(ContentLengthExceededException::class, $e);
    }

    public function testCreatesTitleLengthExceededException(): void
    {
        $payload = ['error' => 'TITLE_LENGTH_EXCEEDED'];
        $e = ExceptionFactory::createFromResponse(400, $payload);
        $this->assertInstanceOf(TitleLengthExceededException::class, $e);
    }

    public function testCreatesDomainMismatchException(): void
    {
        $payload = ['error' => 'DOMAIN_MISMATCH'];
        $e = ExceptionFactory::createFromResponse(400, $payload);
        $this->assertInstanceOf(DomainMismatchException::class, $e);
    }

    public function testCreatesDuplicateUrlException(): void
    {
        $payload = ['error' => 'DUPLICATE_URL'];
        $e = ExceptionFactory::createFromResponse(400, $payload);
        $this->assertInstanceOf(DuplicateUrlException::class, $e);
    }

    public function testCreatesNoChatbotConfiguredException(): void
    {
        $payload = ['error' => 'NO_CHATBOT_CONFIGURED'];
        $e = ExceptionFactory::createFromResponse(400, $payload);
        $this->assertInstanceOf(NoChatbotConfiguredException::class, $e);
    }

    public function testCreatesResourceNotFoundException(): void
    {
        $payload = ['error' => 'NOT_FOUND'];
        $e = ExceptionFactory::createFromResponse(404, $payload);
        $this->assertInstanceOf(ResourceNotFoundException::class, $e);
    }

    public function testFallbackServerExceptionOn500(): void
    {
        $e = ExceptionFactory::createFromResponse(500, ['message' => 'Internal error']);
        $this->assertInstanceOf(ServerException::class, $e);
        $this->assertSame(500, $e->getStatusCode());
    }

    public function testFallbackClientExceptionOn400(): void
    {
        $e = ExceptionFactory::createFromResponse(400, ['message' => 'Bad request']);
        $this->assertInstanceOf(ClientException::class, $e);
        $this->assertSame(400, $e->getStatusCode());
    }
}
