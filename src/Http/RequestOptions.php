<?php

declare(strict_types=1);

namespace Clusterify\Http;

/**
 * Optional per-request settings (bypassing LRU cache, custom headers, timeout overrides).
 */
final readonly class RequestOptions
{
    /**
     * @param bool $noCache Whether to add ?nocache=1 / x-no-cache: true to bypass 1-minute API LRU caches
     * @param int|null $timeoutMs Per-request timeout in milliseconds (overrides client default if set)
     * @param array<string, string> $headers Additional custom headers for this single request
     */
    public function __construct(
        public bool $noCache = false,
        public ?int $timeoutMs = null,
        public array $headers = []
    ) {}

    /**
     * Helper to create a noCache options instance.
     */
    public static function noCache(): self
    {
        return new self(noCache: true);
    }
}
