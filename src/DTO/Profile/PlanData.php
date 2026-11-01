<?php

declare(strict_types=1);

namespace Clusterify\DTO\Profile;

use Clusterify\Support\Payload;
use JsonSerializable;

/**
 * Tenant subscription plan details and capabilities.
 */
final readonly class PlanData implements JsonSerializable
{
    /**
     * @param int $id Plan level ID (1: Starter, 2: Professional)
     * @param string $name Human-readable plan name (e.g. 'PROFESSIONAL Plan')
     * @param bool $isUrlKnowledgeAllowed Whether URL-based knowledge is unlocked
     * @param PlanRestrictions $restrictions Resource limit matrix
     */
    public function __construct(
        public int $id,
        public string $name,
        public bool $isUrlKnowledgeAllowed,
        public PlanRestrictions $restrictions
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $restrictionsData = Payload::map($data, 'restrictions');

        return new self(
            id: Payload::int($data, 'id', 1),
            name: Payload::string($data, 'name', 'Starter Plan'),
            isUrlKnowledgeAllowed: Payload::bool($data, 'is_url_knowledge_allowed', false),
            restrictions: PlanRestrictions::fromArray($restrictionsData)
        );
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     is_url_knowledge_allowed: bool,
     *     restrictions: array<string, int>
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_url_knowledge_allowed' => $this->isUrlKnowledgeAllowed,
            'restrictions' => $this->restrictions->toArray(),
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
