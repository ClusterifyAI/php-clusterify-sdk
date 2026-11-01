<?php

declare(strict_types=1);

namespace Clusterify\DTO\Installation;

use Clusterify\Support\Payload;
use JsonSerializable;

/**
 * Structured instructions and guides for deploying the chatbot.
 */
final readonly class InstallationInfo implements JsonSerializable
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
        $rawGuides = Payload::listOfMaps($data, 'available_guides');
        $availableGuides = array_map(
            static fn(array $guide): IntegrationGuide => IntegrationGuide::fromArray($guide),
            $rawGuides
        );

        $rawAdmin = Payload::map($data, 'admin_guides');
        $adminGuides = AdminGuides::fromArray($rawAdmin);

        $verificationSteps = Payload::listOfStrings($data, 'verification_steps');

        return new self(
            overview: Payload::string($data, 'overview', ''),
            availableGuides: $availableGuides,
            adminGuides: $adminGuides,
            verificationSteps: $verificationSteps,
            securityNotice: Payload::string($data, 'security_notice', '')
        );
    }

    /**
     * @return array{
     *     overview: string,
     *     available_guides: list<array{platform: string, name: string, summary: string, doc_url: string}>,
     *     admin_guides: array<string, string>,
     *     verification_steps: list<string>,
     *     security_notice: string
     * }
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

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
