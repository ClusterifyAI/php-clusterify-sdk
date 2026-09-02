<?php

declare(strict_types=1);

namespace Clusterify\DTO\Installation;

/**
 * Structured instructions and guides for deploying the chatbot.
 */
final readonly class InstallationInfo
{
    /**
     * @param string $overview High-level embedding overview
     * @param list<IntegrationGuide> $availableGuides List of platform-specific installation guides
     * @param AdminGuides $adminGuides Dashboard administrative links
     * @param list<string> $verificationSteps Ordered list of verification steps
     * @param string $securityNotice Security advice regarding public UUID exposure
     */
    public function __construct(
        public string $overview,
        public array $availableGuides,
        public AdminGuides $adminGuides,
        public array $verificationSteps,
        public string $securityNotice
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        /** @var list<array<string, mixed>> $rawGuides */
        $rawGuides = $data['available_guides'] ?? [];
        $availableGuides = array_values(array_map(
            static fn(array $guide): IntegrationGuide => IntegrationGuide::fromArray($guide),
            $rawGuides
        ));

        /** @var array<string, mixed> $rawAdmin */
        $rawAdmin = $data['admin_guides'] ?? [];
        $adminGuides = AdminGuides::fromArray($rawAdmin);

        /** @var list<string> $verificationSteps */
        $verificationSteps = array_values(array_map(
            static fn(mixed $step): string => (string) $step,
            $data['verification_steps'] ?? []
        ));

        return new self(
            overview: (string) ($data['overview'] ?? ''),
            availableGuides: $availableGuides,
            adminGuides: $adminGuides,
            verificationSteps: $verificationSteps,
            securityNotice: (string) ($data['security_notice'] ?? '')
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'overview' => $this->overview,
            'available_guides' => array_map(static fn(IntegrationGuide $g): array => $g->toArray(), $this->availableGuides),
            'admin_guides' => $this->adminGuides->toArray(),
            'verification_steps' => $this->verificationSteps,
            'security_notice' => $this->securityNotice,
        ];
    }
}
