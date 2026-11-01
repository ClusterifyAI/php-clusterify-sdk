<?php

declare(strict_types=1);

namespace Clusterify\DTO\Common;

use Clusterify\Support\Payload;
use JsonSerializable;

/**
 * Pagination metadata returned by paginated API endpoints.
 */
final readonly class Pagination implements JsonSerializable
{
    /**
     * @param int $page Current 1-based page number
     * @param int $limit Max items per page
     * @param int $totalItems Total items available across all pages
     * @param int $totalPages Total number of pages
     */
    public function __construct(
        public int $page,
        public int $limit,
        public int $totalItems,
        public int $totalPages
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            page: Payload::int($data, 'page', 1),
            limit: Payload::int($data, 'limit', 20),
            totalItems: Payload::int($data, 'total_items', 0),
            totalPages: Payload::int($data, 'total_pages', 1)
        );
    }

    /**
     * @return array{page: int, limit: int, total_items: int, total_pages: int}
     */
    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'limit' => $this->limit,
            'total_items' => $this->totalItems,
            'total_pages' => $this->totalPages,
        ];
    }

    /**
     * @return array{page: int, limit: int, total_items: int, total_pages: int}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
