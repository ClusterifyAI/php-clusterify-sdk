<?php

declare(strict_types=1);

namespace Clusterify\Http;

use InvalidArgumentException;

/**
 * Optional per-request settings (bypassing LRU cache, custom headers, timeout overrides).
 */
final readonly class RequestOptions
{
    /**
     * @param bool $noCache Whether to add ?nocache=1 to bypass 1-minute API LRU caches
     * @param int|null $timeoutMs Per-request timeout in milliseconds (overrides client default if set)
     * @param array<string, string> $headers Additional custom headers for this single request
     * @throws InvalidArgumentException
     */
    public function __construct(
        public bool $noCache = false,
        public ?int $timeoutMs = null,
        public array $headers = []
    ) {
        if ($timeoutMs !== null && $timeoutMs <= 0) {
            throw new InvalidArgumentException('Per-request timeout (timeoutMs) must be greater than 0.');
        }
    }

    /**
     * Helper to create a noCache options instance.
     */
    public static function noCache(): self
    {
        return new self(noCache: true);
    }
}
