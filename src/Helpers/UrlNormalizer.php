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
        $cleanDomain = rtrim(trim($chatbotDomain), '/');
        if ($cleanDomain === '') {
            throw new InvalidArgumentException("Invalid chatbot domain: {$chatbotDomain}");
        }

        if (preg_match('#^[a-zA-Z][a-zA-Z0-9+.-]*://#', $cleanDomain) === 1) {
            if (!str_starts_with(strtolower($cleanDomain), 'http://') && !str_starts_with(strtolower($cleanDomain), 'https://')) {
                throw new InvalidArgumentException("Unsupported URL scheme in chatbot domain: {$chatbotDomain}");
            }
        } else {
            $cleanDomain = 'https://' . $cleanDomain;
        }

        $parsedDomain = parse_url($cleanDomain);
        $domainHost = is_array($parsedDomain) ? ($parsedDomain['host'] ?? '') : '';
        $domainPort = is_array($parsedDomain) ? ($parsedDomain['port'] ?? null) : null;

        if ($domainHost === '') {
            throw new InvalidArgumentException("Invalid chatbot domain: {$chatbotDomain}");
        }

        $trimmed = trim($urlOrPath);
        if ($trimmed === '') {
            throw new InvalidArgumentException('URL or path must not be empty.');
        }

        // Reject dangerous or non-HTTP schemes (e.g. javascript:, mailto:, ftp://)
        if (preg_match('#^([a-zA-Z][a-zA-Z0-9+.-]*):(.*)$#', $trimmed, $schemeMatch) === 1) {
            $scheme = strtolower($schemeMatch[1]);
            if ($scheme !== 'http' && $scheme !== 'https') {
                throw new InvalidArgumentException("Unsupported URL scheme '{$scheme}' in '{$urlOrPath}'.");
            }
        }

        // Relative path starting with '/'
        if (str_starts_with($trimmed, '/')) {
            return $cleanDomain . $trimmed;
        }

        // Relative path without scheme
        if (!str_starts_with(strtolower($trimmed), 'http://') && !str_starts_with(strtolower($trimmed), 'https://')) {
            return $cleanDomain . '/' . $trimmed;
        }

        // Full HTTP(S) URL provided - verify host and port match
        $parsedUrl = parse_url($trimmed);
        $urlHost = is_array($parsedUrl) ? ($parsedUrl['host'] ?? '') : '';
        $urlPort = is_array($parsedUrl) ? ($parsedUrl['port'] ?? null) : null;

        if ($urlHost === '' || strcasecmp($urlHost, $domainHost) !== 0) {
            throw new InvalidArgumentException("URL host '{$urlHost}' does not match registered chatbot domain host '{$domainHost}'.");
        }

        if ($urlPort !== $domainPort) {
            $formattedUrlHost = $urlHost . ($urlPort !== null ? ":{$urlPort}" : '');
            $formattedDomainHost = $domainHost . ($domainPort !== null ? ":{$domainPort}" : '');
            throw new InvalidArgumentException("URL port '{$formattedUrlHost}' does not match registered chatbot domain '{$formattedDomainHost}'.");
        }

        return $trimmed;
    }
}
