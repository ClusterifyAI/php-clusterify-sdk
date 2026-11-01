<?php

declare(strict_types=1);

namespace Clusterify\Tests\Unit\Helpers;

use Clusterify\Helpers\MarkdownSyncHelper;
use PHPUnit\Framework\TestCase;

final class MarkdownSyncHelperTest extends TestCase
{
    public function testExtractTitleFromFrontmatter(): void
    {
        $md = "---\ntitle: 'Getting Started Guide'\nauthor: Clusterify\n---\n# Some Content\nHello World";
        $title = MarkdownSyncHelper::extractTitle($md);
        $this->assertSame('Getting Started Guide', $title);
    }

    public function testExtractTitleWithApostropheInsideDoubleQuotes(): void
    {
        $md = "---\ntitle: \"What's New in v2\"\n---\n# Fallback";
        $this->assertSame("What's New in v2", MarkdownSyncHelper::extractTitle($md));
    }

    public function testExtractTitleDoesNotBleedPastFrontmatterIntoMarkdownRule(): void
    {
        $md = "---\nauthor: Clusterify\n---\n\n# Real Heading\n\ntitle: Fake Body Line\n\n---\nFooter";
        $this->assertSame('Real Heading', MarkdownSyncHelper::extractTitle($md));
    }

    public function testExtractTitleFromH1(): void
    {
        $md = "# Platform Overview\nThis is an introductory guide.";
        $title = MarkdownSyncHelper::extractTitle($md);
        $this->assertSame('Platform Overview', $title);
    }

    public function testScanDirectoryReturnsDeterministicSortedRoutes(): void
    {
        $tempDir = sys_get_temp_dir() . '/clusterify_test_docs_' . uniqid();
        mkdir($tempDir, 0777, true);
        mkdir($tempDir . '/guides', 0777, true);

        file_put_contents($tempDir . '/guides/zebra.md', "# Zebra\nZ");
        file_put_contents($tempDir . '/index.md', "# Home\nWelcome to docs");
        file_put_contents($tempDir . '/guides/install.md', "---\ntitle: Installation\n---\nInstall instructions");

        try {
            $files = MarkdownSyncHelper::scanDirectory($tempDir, '/docs');
            $this->assertCount(3, $files);

            $routes = array_column($files, 'route');
            $this->assertSame([
                '/docs',
                '/docs/guides/install',
                '/docs/guides/zebra',
            ], $routes);
        } finally {
            @unlink($tempDir . '/guides/zebra.md');
            @unlink($tempDir . '/index.md');
            @unlink($tempDir . '/guides/install.md');
            @rmdir($tempDir . '/guides');
            @rmdir($tempDir);
        }
    }
}
