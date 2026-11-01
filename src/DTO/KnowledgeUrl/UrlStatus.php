<?php

declare(strict_types=1);

namespace Clusterify\DTO\KnowledgeUrl;

/**
 * Filter status options for URL-based knowledge entries.
 */
enum UrlStatus: string
{
    case All = 'all';
    case Enabled = 'enabled';
    case Disabled = 'disabled';
}

