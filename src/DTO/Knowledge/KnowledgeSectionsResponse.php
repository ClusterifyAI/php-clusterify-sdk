<?php

declare(strict_types=1);

namespace Clusterify\DTO\Knowledge;

/**
 * Response envelope returned by reordering or replacing knowledge sections.
 */
final readonly class KnowledgeSectionsResponse
{
    /**
     * @param bool $success Whether the request succeeded
     * @param list<KnowledgeSection> $sections Updated list of sections
     * @param int|null $totalSections Total count of sections
     * @param string|null $message API message
     */
    public function __construct(
        public bool $success,
        public array $sections,
        public ?int $totalSections = null,
        public ?string $message = null
    ) {}

    /**
     * @param array<string, mixed> $data
     * @param string|null $message
     */
    public static function fromArray(array $data, ?string $message = null): self
    {
        /** @var array<string, mixed> $dataNode */
        $dataNode = $data['data'] ?? $data;
        /** @var list<array<string, mixed>> $rawSections */
        $rawSections = $dataNode['sections'] ?? [];
        $sections = array_values(array_map(
            static fn(array $sec): KnowledgeSection => KnowledgeSection::fromArray($sec),
            $rawSections
        ));

        $totalSections = isset($dataNode['total_sections']) ? (int) $dataNode['total_sections'] : count($sections);
        $msg = $message ?? (isset($data['message']) && is_string($data['message']) ? $data['message'] : null);

        return new self(
            success: (bool) ($data['success'] ?? true),
            sections: $sections,
            totalSections: $totalSections,
            message: $msg
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data' => [
                'sections' => array_map(static fn(KnowledgeSection $s): array => $s->toArray(), $this->sections),
                'total_sections' => $this->totalSections,
            ],
        ];
    }
}
