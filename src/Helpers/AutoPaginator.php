<?php

declare(strict_types=1);

namespace Clusterify\Helpers;

use Generator;

/**
 * Generic lazy paginator helper for batch processing API collections.
 */
final class AutoPaginator
{
    /**
     * Lazily iterate through all pages of a paginated API resource.
     *
     * @template T
     * @param callable(int): array{items: list<T>, total_pages: int} $fetcher Function receiving 1-based page number and returning items and total pages
     * @return Generator<int, T>
     */
    public static function iterate(callable $fetcher): Generator
    {
        $page = 1;

        while (true) {
            $result = $fetcher($page);
            $items = $result['items'] ?? [];
            $totalPages = $result['total_pages'] ?? 1;

            foreach ($items as $item) {
                yield $item;
            }

            if ($page >= $totalPages || empty($items)) {
                break;
            }

            $page++;
        }
    }
}
