<?php

declare(strict_types=1);

namespace Clusterify\Helpers;

use Clusterify\ClusterifyClient;
use Clusterify\DTO\KnowledgeUrl\BatchUpsertUrlResponse;
use Clusterify\DTO\KnowledgeUrl\UpsertUrlRequest;
use DirectoryIterator;
use InvalidArgumentException;
use SplFileInfo;

/**
 * High-level utility to scan local documentation directories and synchronize markdown documents with Clusterify.
 */
final class MarkdownSyncHelper
{
    /**
     * Scan a directory recursively for markdown files (.md, .markdown).
     *
     * @param string $directoryPath Path to the local documentation directory
     * @param string $baseRoute Optional base route prefix (e.g. '/docs')
     * @return list<array{path: string, route: string, title: string, content: string}>
     */
    public static function scanDirectory(string $directoryPath, string $baseRoute = ''): array
    {
        if (!is_dir($directoryPath)) {
            throw new InvalidArgumentException("Directory not found: {$directoryPath}");
        }

        $realDir = realpath($directoryPath);
        if ($realDir === false) {
            throw new InvalidArgumentException("Could not resolve real path for directory: {$directoryPath}");
        }

        $results = [];
        self::scanRecursive($realDir, $realDir, $baseRoute, $results);

        return $results;
    }

    /**
     * Scan a directory of markdown files and batch upsert them to Clusterify URL Knowledge.
     *
     * @param ClusterifyClient $client Authenticated Clusterify client instance
     * @param string $directoryPath Local directory containing markdown files
     * @param string $chatbotDomain Target registered chatbot domain (e.g. 'https://example.com')
     * @param string $baseRoute Optional URL path prefix (e.g. '/docs')
     * @param int $chunkSize Batch chunk size (max 50)
     * @return list<BatchUpsertUrlResponse>
     */
    public static function syncDirectoryToUrls(
        ClusterifyClient $client,
        string $directoryPath,
        string $chatbotDomain,
        string $baseRoute = '',
        int $chunkSize = 25
    ): array {
        $files = self::scanDirectory($directoryPath, $baseRoute);
        $items = [];

        foreach ($files as $file) {
            $fullUrl = UrlNormalizer::normalize($file['route'], $chatbotDomain);
            $items[] = new UpsertUrlRequest(
                url: $fullUrl,
                content: $file['content'],
                isEnabled: true
            );
        }

        if (empty($items)) {
            return [];
        }

        $chunks = array_chunk($items, max(1, min(50, $chunkSize)));
        $responses = [];

        foreach ($chunks as $chunk) {
            $responses[] = $client->knowledgeUrl()->batchUpsert($chunk);
        }

        return $responses;
    }

    /**
     * Extract the main title from markdown content (from YAML frontmatter or first # Heading).
     *
     * @param string $content
     * @return string
     */
    public static function extractTitle(string $content): string
    {
        // Check YAML frontmatter title: "title: ..."
        if (preg_match('/^---\s*\r?\n.*?\btitle:\s*(?:["\']([^"\']+)["\']|([^\r\n]+)).*?\r?\n---/s', $content, $matches)) {
            $extracted = !empty($matches[1]) ? $matches[1] : ($matches[2] ?? '');
            return trim($extracted, " \"'");
        }

        // Check first H1 heading: "# My Title"
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }

        return 'Documentation';
    }

    /**
     * @param string $rootDir
     * @param string $currentDir
     * @param string $baseRoute
     * @param list<array{path: string, route: string, title: string, content: string}> $results
     */
    private static function scanRecursive(string $rootDir, string $currentDir, string $baseRoute, array &$results): void
    {
        $iterator = new DirectoryIterator($currentDir);

        foreach ($iterator as $item) {
            if ($item->isDot()) {
                continue;
            }

            if ($item->isDir()) {
                self::scanRecursive($rootDir, $item->getPathname(), $baseRoute, $results);
                continue;
            }

            if ($item->isFile()) {
                $ext = strtolower($item->getExtension());
                if ($ext === 'md' || $ext === 'markdown') {
                    $filePath = $item->getPathname();
                    $content = (string) file_get_contents($filePath);
                    $relative = substr($filePath, strlen($rootDir));
                    $route = self::filePathToRoute($relative, $baseRoute);
                    $title = self::extractTitle($content);

                    $results[] = [
                        'path' => $filePath,
                        'route' => $route,
                        'title' => $title,
                        'content' => $content,
                    ];
                }
            }
        }
    }

    /**
     * Convert relative file path to clean web route (e.g. '/intro/getting-started.md' -> '/docs/intro/getting-started').
     */
    private static function filePathToRoute(string $relativePath, string $baseRoute): string
    {
        $clean = preg_replace('/(\.md|\.markdown)$/i', '', $relativePath);
        $clean = preg_replace('/(\/index)$/i', '', (string) $clean);
        $clean = str_replace('\\', '/', (string) $clean);

        $cleanPath = trim((string) $clean, '/');
        $cleanBase = trim($baseRoute, '/');

        if ($cleanPath === '') {
            return $cleanBase !== '' ? '/' . $cleanBase : '/';
        }

        if ($cleanBase === '') {
            return '/' . $cleanPath;
        }

        return '/' . $cleanBase . '/' . $cleanPath;
    }
}
