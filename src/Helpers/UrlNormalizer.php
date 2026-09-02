<?php

declare(strict_types=1);

namespace Clusterify\Helpers;

use InvalidArgumentException;

/**
 * Helper to validate, format, and normalize URLs against chatbot domains.
 */
final class UrlNormalizer
{
    /**
     * Normalize a URL path or full URL against a base chatbot domain.
     *
     * @param string $urlOrPath Full URL (e.g. 'https://example.com/docs') or path (e.g. '/docs')
     * @param string $chatbotDomain Registered chatbot domain (e.g. 'https://example.com')
     * @return string Fully-qualified normalized URL
     * @throws InvalidArgumentException
     */
    public static function normalize(string $urlOrPath, string $chatbotDomain): string
    {
        $cleanDomain = rtrim($chatbotDomain, '/');
        if (!str_starts_with($cleanDomain, 'http://') && !str_starts_with($cleanDomain, 'https://')) {
            $cleanDomain = 'https://' . $cleanDomain;
        }

        $parsedDomain = parse_url($cleanDomain);
        $domainHost = $parsedDomain['host'] ?? '';

        if ($domainHost === '') {
            throw new InvalidArgumentException("Invalid chatbot domain: {$chatbotDomain}");
        }

        $trimmed = trim($urlOrPath);

        // If relative path
        if (str_starts_with($trimmed, '/')) {
            return $cleanDomain . $trimmed;
        }

        if (!str_starts_with($trimmed, 'http://') && !str_starts_with($trimmed, 'https://')) {
            // Treat as relative path
            return $cleanDomain . '/' . $trimmed;
        }

        // Full URL provided - verify host matches
        $parsedUrl = parse_url($trimmed);
        $urlHost = $parsedUrl['host'] ?? '';

        if (strcasecmp($urlHost, $domainHost) !== 0) {
            throw new InvalidArgumentException("URL host '{$urlHost}' does not match registered chatbot domain host '{$domainHost}'.");
        }

        return $trimmed;
    }
}
