<?php

declare(strict_types=1);

namespace Clusterify\Exceptions;

use Throwable;

/**
 * Thrown when an endpoint or feature requires a higher subscription plan (HTTP 403: FORBIDDEN_PLAN).
 */
class ForbiddenPlanException extends ClientException
{
    protected readonly ?int $currentPlan;
    protected readonly ?int $requiredPlan;
    protected readonly ?string $upgradeUrl;
    protected readonly ?string $billingPage;

    /**
     * @param string $message
     * @param array<string, mixed> $details
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message = 'This feature requires a higher subscription plan.',
        array $details = [],
        ?Throwable $previous = null
    ) {
        parent::__construct(
            message: $message,
            errorCode: 'FORBIDDEN_PLAN',
            statusCode: 403,
            details: $details,
            previous: $previous
        );

        $this->currentPlan = isset($details['current_plan']) ? (int) $details['current_plan'] : null;
        $this->requiredPlan = isset($details['required_plan']) ? (int) $details['required_plan'] : null;
        $this->upgradeUrl = isset($details['upgrade_url']) ? (string) $details['upgrade_url'] : null;
        $this->billingPage = isset($details['billing_page']) ? (string) $details['billing_page'] : null;
    }

    /**
     * Get the tenant's current plan ID if provided in details.
     */
    public function getCurrentPlan(): ?int
    {
        return $this->currentPlan;
    }

    /**
     * Get the required plan ID to use this feature.
     */
    public function getRequiredPlan(): ?int
    {
        return $this->requiredPlan;
    }

    /**
     * Get the direct upgrade URL if provided in details.
     */
    public function getUpgradeUrl(): ?string
    {
        return $this->upgradeUrl;
    }

    /**
     * Get the dashboard billing path (e.g. '/billing').
     */
    public function getBillingPage(): ?string
    {
        return $this->billingPage;
    }
}
