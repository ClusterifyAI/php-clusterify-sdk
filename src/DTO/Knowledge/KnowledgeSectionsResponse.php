<?php

declare(strict_types=1);

namespace Clusterify\DTO\Knowledge;

use Clusterify\Support\Payload;
use JsonSerializable;

/**
 * Response envelope returned by reordering or replacing knowledge sections.
 */
final readonly class KnowledgeSectionsResponse implements JsonSerializable
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
        $dataNode = Payload::unwrapData($data);
        $rawSections = Payload::listOfMaps($dataNode, 'sections');
        $sections = array_map(
            static fn(array $sec): KnowledgeSection => KnowledgeSection::fromArray($sec),
            $rawSections
        );

        $totalSections = Payload::nullableInt($dataNode, 'total_sections') ?? count($sections);
        $msg = $message ?? Payload::nullableString($data, 'message');

        return new self(
            success: Payload::bool($data, 'success', true),
            sections: $sections,
            totalSections: $totalSections,
            message: $msg
        );
    }

    /**
     * @return array{
     *     success: bool,
     *     message: string|null,
     *     data: array{
     *         sections: list<array{id: string, position: int, title: string, content: string, isOpen: bool}>,
     *         total_sections: int|null
     *     }
     * }
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

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
