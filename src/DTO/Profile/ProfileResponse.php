<?php

declare(strict_types=1);

namespace Clusterify\DTO\Profile;

/**
 * Profile and subscription plan response envelope.
 */
final readonly class ProfileResponse
{
    /**
     * @param bool $success Whether the request succeeded
     * @param ProfileData $profile User profile information
     * @param PlanData $plan Subscription plan and limits
     */
    public function __construct(
        public bool $success,
        public ProfileData $profile,
        public PlanData $plan
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed> $dataNode */
        $dataNode = $data['data'] ?? $data;
        /** @var array<string, mixed> $profileNode */
        $profileNode = $dataNode['profile'] ?? [];
        /** @var array<string, mixed> $planNode */
        $planNode = $dataNode['plan'] ?? [];

        return new self(
            success: (bool) ($data['success'] ?? true),
            profile: ProfileData::fromArray($profileNode),
            plan: PlanData::fromArray($planNode)
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => [
                'profile' => $this->profile->toArray(),
                'plan' => $this->plan->toArray(),
            ],
        ];
    }
}
