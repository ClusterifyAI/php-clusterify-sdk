<?php

declare(strict_types=1);

namespace Clusterify\DTO\Profile;

use Clusterify\Support\Payload;
use JsonSerializable;

/**
 * Profile and subscription plan response envelope.
 */
final readonly class ProfileResponse implements JsonSerializable
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
        $dataNode = Payload::unwrapData($data);
        $profileNode = Payload::map($dataNode, 'profile');
        $planNode = Payload::map($dataNode, 'plan');

        return new self(
            success: Payload::bool($data, 'success', true),
            profile: ProfileData::fromArray($profileNode),
            plan: PlanData::fromArray($planNode)
        );
    }

    /**
     * @return array{
     *     success: bool,
     *     data: array{
     *         profile: array<string, string|null>,
     *         plan: array<string, mixed>
     *     }
     * }
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

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
