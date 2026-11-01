<?php

declare(strict_types=1);

namespace Clusterify\Tests\Unit\Helpers;

use Clusterify\Helpers\AutoPaginator;
use PHPUnit\Framework\TestCase;

final class AutoPaginatorTest extends TestCase
{
    public function testIteratesAllPagesLazily(): void
    {
        $fetchedPages = [];

        $generator = AutoPaginator::iterate(static function (int $page) use (&$fetchedPages): array {
            $fetchedPages[] = $page;

            return match ($page) {
                1 => ['items' => ['a', 'b'], 'total_pages' => 3],
                2 => ['items' => ['c', 'd'], 'total_pages' => 3],
                default => ['items' => ['e'], 'total_pages' => 3],
            };
        });

        // Nothing fetched before iteration starts
        $this->assertSame([], $fetchedPages);

        $items = iterator_to_array($generator, false);
        $this->assertSame(['a', 'b', 'c', 'd', 'e'], $items);
        $this->assertSame([1, 2, 3], $fetchedPages);
    }

    public function testStopsImmediatelyOnEmptyItems(): void
    {
        $calls = 0;
        $generator = AutoPaginator::iterate(static function () use (&$calls): array {
            $calls++;
            return ['items' => [], 'total_pages' => 5];
        });

        $this->assertSame([], iterator_to_array($generator, false));
        $this->assertSame(1, $calls);
    }
}

