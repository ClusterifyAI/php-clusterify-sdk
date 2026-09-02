# Clusterify.AI Chatbot PHP SDK (`clusterify/chatbot-sdk`)

[![Latest Version](https://img.shields.io/packagist/v/clusterify/chatbot-sdk.svg?style=flat-square)](https://packagist.org/packages/clusterify/chatbot-sdk)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D%208.2-8892BF.svg?style=flat-square)](https://php.net/)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%208-brightgreen.svg?style=flat-square)](phpstan.neon)
[![Tests](https://img.shields.io/badge/Tests-100%25%20Passing-brightgreen.svg?style=flat-square)](phpunit.xml)

Official enterprise-grade PHP SDK for the **[Clusterify.AI](https://clusterify.ai) Chatbot Dashboard External API**. Built with modern PHP 8.2+ strict typing, PSR standards, typed data transfer objects (DTOs), and resilient error handling.

Designed as the authoritative foundation for backend integrations, synchronization daemons, CI/CD documentation pipelines, CLI tools, and CMS extensions (WordPress, Magento 2, Laravel, Symfony).

---

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Authentication Mechanisms](#authentication-mechanisms)
- [Client Configuration & Builder](#client-configuration--builder)
- [API Reference & Examples](#api-reference--examples)
  - [1. Ping & Healthcheck](#1-ping--healthcheck)
  - [2. Profile & Subscription Plans](#2-profile--subscription-plans)
  - [3. Chatbot Configuration](#3-chatbot-configuration)
  - [4. Installation Snippet & Guides](#4-installation-snippet--guides)
  - [5. Simple Knowledge Base (`knowledge_builder`)](#5-simple-knowledge-base-knowledge_builder)
  - [6. URL-Based Knowledge Base (`knowledge_url`)](#6-url-based-knowledge-base-knowledge_url---professional-plan)
- [Cache Control & Bypassing](#cache-control--bypassing)
- [Enterprise Error Handling & Error Codes](#enterprise-error-handling--error-codes)
- [Advanced Features & Helpers](#advanced-features--helpers)
  - [Memory-Safe Auto-Pagination (Generators)](#memory-safe-auto-pagination-generators)
  - [Automated Markdown Sync for CI/CD](#automated-markdown-sync-for-cicd)
  - [Configurable Retries with Jitter](#configurable-retries-with-jitter)
  - [Zero-Credential Secret Masking](#zero-credential-secret-masking)
- [Framework Integration Recipes](#framework-integration-recipes)
  - [Laravel](#laravel)
  - [Symfony](#symfony)
- [Development & Testing](#development--testing)
- [License](#license)

---

## Features

- **PHP 8.2+ Modern Architecture**: `readonly` classes, constructor property promotion, strict types, and backed enums.
- **Strictly Typed DTOs**: Every request and response is represented by an immutable Data Transfer Object with full IDE auto-completion.
- **PHP-FIG PSR Compliant**:
  - **PSR-4**: Autoloading.
  - **PSR-7 & PSR-17**: HTTP Messages and Factories.
  - **PSR-18**: Pluggable HTTP Client (Guzzle 7 by default, or auto-discovered via `php-http/discovery`).
  - **PSR-3**: Pluggable Logger with sensitive token redaction.
- **Enterprise Error Hierarchy**: Domain-specific typed exceptions carrying machine-readable error codes, HTTP statuses, and actionable recovery details (such as required plan, upgrade URLs, and validation errors).
- **Resilience**: Built-in exponential backoff with full jitter and RFC 7231 `Retry-After` header parsing for rate limits (`429`) and server errors (`5xx`).
- **Developer Ergonomics**: High-level utilities for directory markdown syncing and lazy auto-pagination.

---

## Requirements

- **PHP**: `^8.2` or higher
- **cURL Extension**: Enabled
- **JSON Extension**: Enabled
- **Composer**: For dependency management

---

## Installation

Install the package via Composer:

```bash
composer require clusterify/chatbot-sdk
```

---

## Quick Start

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Clusterify\ClusterifyClient;

// 1. Initialize client with your Public Key and Secret Key
$client = ClusterifyClient::create(
    publicKey: 'pk_live_your_public_key_here',
    secretKey: 'sk_live_your_secret_key_here'
);

// 2. Perform a fast healthcheck
$ping = $client->ping();
echo "Status: {$ping->status} | Authenticated: " . ($ping->authenticated ? 'Yes' : 'No') . "\n";

// 3. Retrieve user profile and subscription plan
$profile = $client->profile()->get();
echo "Welcome, {$profile->profile->name}! Active Plan: {$profile->plan->name}\n";
```

---

## Authentication Mechanisms

The SDK supports three authentication formats. **Dedicated Custom Headers** is the recommended default.

```php
use Clusterify\Auth\AuthMethod;
use Clusterify\ClusterifyClient;

// Option A: Dedicated Custom Headers (Recommended Default)
// Sends: x-public-key and x-secret-key
$clientA = ClusterifyClient::create($publicKey, $secretKey, authMethod: AuthMethod::CustomHeaders);

// Option B: Bearer Token
// Sends: x-public-key and Authorization: Bearer <secretKey>
$clientB = ClusterifyClient::create($publicKey, $secretKey, authMethod: AuthMethod::Bearer);

// Option C: HTTP Basic Auth
// Sends: Authorization: Basic Base64(publicKey:secretKey)
$clientC = ClusterifyClient::create($publicKey, $secretKey, authMethod: AuthMethod::Basic);
```

---

## Client Configuration & Builder

For enterprise applications, use `ClusterifyClient::builder()` to customize timeouts, logging, retries, or inject custom PSR-18 clients:

```php
use Clusterify\ClusterifyClient;
use Clusterify\Support\RetryPolicy;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('clusterify');
$logger->pushHandler(new StreamHandler(__DIR__ . '/clusterify.log'));

$client = ClusterifyClient::builder()
    ->withCredentials('pk_live_...', 'sk_live_...')
    ->withBaseUrl('https://api.clusterify.ai')
    ->withTimeout(15000)          // 15 seconds request timeout
    ->withConnectTimeout(5000)     // 5 seconds connection timeout
    ->withRetryPolicy(new RetryPolicy(
        maxRetries: 4,
        baseDelayMs: 500,
        maxDelayMs: 8000,
        jitter: 0.25
    ))
    ->withLogger($logger)
    ->withHeader('x-custom-tracking', 'ci-sync-daemon')
    ->build();
```

---

## API Reference & Examples

### 1. Ping & Healthcheck
Fast (`< 5ms`) healthcheck and credential verification endpoint.

```php
$ping = $client->ping();

echo $ping->success;       // true
echo $ping->status;        // "healthy"
echo $ping->authenticated; // true
echo $ping->timestamp;     // "2026-08-31T21:15:00.000Z"
```

---

### 2. Profile & Subscription Plans
Retrieves profile metadata and plan restrictions matrix.

```php
$res = $client->profile()->get();

// Profile Details
echo $res->profile->name;     // "Zsolt Szalay"
echo $res->profile->email;    // "zsolt@clusterify.ai"
echo $res->profile->country;  // "United States"
echo $res->profile->timezone; // "America/New_York"

// Plan & Restrictions
echo $res->plan->id;                       // 2 (Professional)
echo $res->plan->name;                     // "PROFESSIONAL Plan"
echo $res->plan->isUrlKnowledgeAllowed;    // true
echo $res->plan->restrictions->maxKnowledgeGeneralSections; // 25
echo $res->plan->restrictions->maxKnowledgeUrls;            // 1000
echo $res->plan->restrictions->maxKnowledgeUrlsKnowledgeLength; // 20000
```

---

### 3. Chatbot Configuration
Fetches sanitized chatbot configuration (zero secret key exposure).

```php
$res = $client->chatbot()->get();

$chatbot = $res->chatbot;
echo $chatbot->id;               // 50
echo $chatbot->domain;           // "https://example.com"
echo $chatbot->publicUuid;       // "78d2c63c-2c11-4d4b-adec-63dbdfb5c60e"
echo $chatbot->aiCompany;        // "Gemini"
echo $chatbot->aiModel;          // "gemini-3.7-flash"
echo $chatbot->aiThinkingLevel;  // "zero"
echo $chatbot->isEnabled;        // true

// Visual styling details
echo $chatbot->details->title;           // "AI Assistant"
echo $chatbot->details->headerBgColor;   // "#222222"
echo $chatbot->details->headerTextColor; // "#ffffff"
echo $chatbot->details->popupRadius;     // "15"
```

---

### 4. Installation Snippet & Guides
Retrieves the embeddable `<script>` tag and comprehensive integration guides for CMS platforms.

```php
$res = $client->installation()->get();

// Embed Code
echo $res->htmlSnippet;
// Output: <script id="clusterify-chatbot-script">...</script>

// Public UUID
echo $res->publicUuid; // "78d2c63c-2c11-4d4b-adec-63dbdfb5c60e"

// Platform-Specific Installation Guides
foreach ($res->information->availableGuides as $guide) {
    echo "Platform: {$guide->name}\n";
    echo "Summary: {$guide->summary}\n";
    echo "Documentation: {$guide->docUrl}\n\n";
}

// Verification Steps
foreach ($res->information->verificationSteps as $step) {
    echo "{$step}\n";
}
```

---

### 5. Simple Knowledge Base (`knowledge_builder`)
Global website knowledge base available to all subscription plans.

#### List Sections
```php
$kb = $client->knowledge()->list(search: 'return');

foreach ($kb->sections as $section) {
    echo "[#{$section->position}] {$section->title} (Active: " . ($section->isOpen ? 'Yes' : 'No') . ")\n";
    echo "Content: {$section->content}\n\n";
}

echo "Used: {$kb->usage->totalSections} / {$kb->usage->maxSections} sections\n";
```

#### Fetch Single Section
```php
$section = $client->knowledge()->get('kb_1700000000_abcde');
echo $section->title;
```

#### Create New Section
```php
$newSection = $client->knowledge()->create(
    title: 'Return Policy',
    content: 'Customers can return items within 30 days of receipt in original condition.',
    isOpen: true
);
echo "Created ID: {$newSection->id}\n";
```

#### Update Section
```php
$updatedSection = $client->knowledge()->update(
    id: 'kb_1700000000_abcde',
    title: 'Updated Return & Refund Policy',
    content: 'Returns accepted within 45 days for full refund.'
);
```

#### Toggle Section Status
```php
$client->knowledge()->toggle('kb_1700000000_abcde', isOpen: false);
```

#### Reorder Sections
```php
$reordered = $client->knowledge()->reorder([
    'kb_1700000001_fghij',
    'kb_1700000000_abcde',
]);
```

#### Batch Replace All Sections
```php
$replaced = $client->knowledge()->replaceAll([
    ['title' => 'Main Context', 'content' => 'About our company...', 'is_open' => true],
    ['title' => 'Services', 'content' => 'Consulting and development...', 'is_open' => true],
]);
```

#### Delete Section
```php
// Single delete
$client->knowledge()->delete('kb_1700000000_abcde');

// Bulk delete
$client->knowledge()->bulkDelete([
    'kb_1700000000_abcde',
    'kb_1700000001_fghij'
]);
```

---

### 6. URL-Based Knowledge Base (`knowledge_url`) — *Professional Plan*
Deep, page-specific markdown knowledge injected dynamically when visitors browse registered URLs.

#### Query & Filter URL Records
```php
use Clusterify\DTO\KnowledgeUrl\KnowledgeUrlQuery;

$query = KnowledgeUrlQuery::create()
    ->setPage(1)
    ->setLimit(20)
    ->setStatus('enabled')
    ->setSearch('pricing');

$list = $client->knowledgeUrl()->list($query);

foreach ($list->items as $item) {
    echo "ID: {$item->id} | URL: {$item->url}\n";
    echo "Has Embedding: " . ($item->hasEmbedding ? 'Yes' : 'No') . "\n";
}

echo "Total URLs: {$list->stats->total} / {$list->stats->maxUrls}\n";
```

#### Smart Upsert by URL
Creates the record if it does not exist, or updates it if it already exists:

```php
$item = $client->knowledgeUrl()->upsert(
    url: 'https://example.com/pricing',
    content: "# Pricing & Plans\n- Starter: $18/mo\n- Professional: $37/mo",
    isEnabled: true
);

echo "Upserted ID: {$item->id} for {$item->url}\n";
```

#### Batch Upsert Multiple URLs
```php
$response = $client->knowledgeUrl()->batchUpsert([
    [
        'url' => 'https://example.com/faq',
        'content' => "# Frequently Asked Questions\n...",
        'is_enabled' => true,
    ],
    [
        'url' => 'https://example.com/contact',
        'content' => "# Contact Support\n...",
        'is_enabled' => true,
    ],
]);

echo "Processed: {$response->processed}\n";
echo "Errors: {$response->errorsCount}\n";

foreach ($response->results as $result) {
    echo "[{$result->action}] {$result->item->url} (ID: {$result->item->id})\n";
}
```

#### Delete URL Records
```php
// Delete by ID
$client->knowledgeUrl()->delete(42);

// Bulk delete by IDs and/or URLs
$client->knowledgeUrl()->bulkDelete(
    ids: [42, 43],
    urls: ['https://example.com/deprecated-page']
);
```

---

## Cache Control & Bypassing

The Clusterify API implements 1-minute in-memory LRU caches for maximum read performance. To bypass cache reads, pass `RequestOptions::noCache()`:

```php
use Clusterify\Http\RequestOptions;

// Bypass 1-minute LRU cache
$freshProfile = $client->profile()->get(RequestOptions::noCache());

// Or pass with list requests
$freshKb = $client->knowledge()->list(noCache: RequestOptions::noCache());
```

---

## Enterprise Error Handling & Error Codes

When an API call fails, the SDK converts the response envelope into a specific, typed exception:

```php
use Clusterify\Exceptions\AuthenticationException;
use Clusterify\Exceptions\AuthorizationException;
use Clusterify\Exceptions\ForbiddenPlanException;
use Clusterify\Exceptions\PlanLimitExceededException;
use Clusterify\Exceptions\ContentLengthExceededException;
use Clusterify\Exceptions\DomainMismatchException;
use Clusterify\Exceptions\ValidationException;
use Clusterify\Exceptions\RateLimitExceededException;
use Clusterify\Exceptions\ResourceNotFoundException;
use Clusterify\Exceptions\ClusterifyException;

try {
    $client->knowledgeUrl()->upsert('https://example.com/pricing', '# Pricing');
} catch (ForbiddenPlanException $e) {
    // 403 FORBIDDEN_PLAN
    echo "Feature requires Plan {$e->getRequiredPlan()} (Current: {$e->getCurrentPlan()}).\n";
    echo "Upgrade at: {$e->getUpgradeUrl()}\n";
} catch (PlanLimitExceededException $e) {
    // 400 PLAN_LIMIT_EXCEEDED
    echo "Resource limit reached: " . $e->getMessage() . "\n";
} catch (RateLimitExceededException $e) {
    // 429 RATE_LIMIT_EXCEEDED
    echo "Rate limit exceeded. Retry after: {$e->getRetryAfter()} seconds.\n";
} catch (DomainMismatchException $e) {
    // 400 DOMAIN_MISMATCH
    echo "Domain mismatch: " . $e->getMessage() . "\n";
} catch (ValidationException $e) {
    // 400 VALIDATION_ERROR
    echo "Validation errors:\n";
    print_r($e->getErrors());
} catch (AuthenticationException $e) {
    // 401 INVALID_API_KEYS / MISSING_API_KEYS
    echo "Authentication failed: " . $e->getMessage() . "\n";
} catch (AuthorizationException $e) {
    // 403 API_KEY_DISABLED / USER_DISABLED / USER_NOT_VERIFIED
    echo "Access forbidden: " . $e->getMessage() . "\n";
} catch (ResourceNotFoundException $e) {
    // 404 NOT_FOUND
    echo "Resource not found: " . $e->getMessage() . "\n";
} catch (ClusterifyException $e) {
    // Generic fallback
    echo "Clusterify SDK Exception [{$e->getErrorCode()}]: " . $e->getMessage() . "\n";
}
```

### Complete Error Code Catalog

| Error Code | HTTP Status | Exception Class | Description & Resolution |
| :--- | :--- | :--- | :--- |
| `MISSING_API_KEYS` | `401 Unauthorized` | `AuthenticationException` | Missing `x-public-key` or `x-secret-key` headers. |
| `INVALID_API_KEYS` | `401 Unauthorized` | `AuthenticationException` | Public key not found or secret key hash mismatch. |
| `API_KEY_DISABLED` | `403 Forbidden` | `AuthorizationException` | API key disabled in `/api-key` page. Toggle it ON. |
| `USER_DISABLED` | `403 Forbidden` | `AuthorizationException` | Tenant account is disabled. Contact support. |
| `USER_NOT_VERIFIED` | `403 Forbidden` | `AuthorizationException` | User email address has not been verified. |
| `FORBIDDEN_PLAN` | `403 Forbidden` | `ForbiddenPlanException` | Feature requires Professional Plan (`plan >= 2`). |
| `NO_CHATBOT_CONFIGURED`| `400 Bad Request` | `NoChatbotConfiguredException` | No chatbot registered. Configure a domain in dashboard. |
| `PLAN_LIMIT_EXCEEDED` | `400 Bad Request` | `PlanLimitExceededException` | Maximum sections (15/25) or URLs (1000) reached. |
| `CONTENT_LENGTH_EXCEEDED`| `400 Bad Request` | `ContentLengthExceededException` | Content exceeds character length (7K/10K for KB, 20K for URLs). |
| `TITLE_LENGTH_EXCEEDED`| `400 Bad Request` | `TitleLengthExceededException` | Section title exceeds 100 characters. |
| `DOMAIN_MISMATCH` | `400 Bad Request` | `DomainMismatchException` | URL hostname does not match registered chatbot domain. |
| `DUPLICATE_URL` | `400 Bad Request` | `DuplicateUrlException` | URL already registered under another record ID. |
| `NOT_FOUND` | `404 Not Found` | `ResourceNotFoundException` | Section ID or Knowledge URL record not found. |
| `VALIDATION_ERROR` | `400 Bad Request` | `ValidationException` | Payload schema validation failed. |
| `RATE_LIMIT_EXCEEDED` | `429 Too Many Requests` | `RateLimitExceededException` | Rate limit hit (60 req/min on Starter, 120 req/min on Pro). |

---

## Advanced Features & Helpers

### Memory-Safe Auto-Pagination (Generators)
Effortlessly stream through hundreds or thousands of URL knowledge records without buffering entire collections into memory:

```php
$query = ['status' => 'enabled'];

// Automatically requests next pages on demand
foreach ($client->knowledgeUrl()->autoPaginate($query) as $record) {
    echo "Processing URL [ID: {$record->id}]: {$record->url}\n";
}
```

---

### Automated Markdown Sync for CI/CD
Automatically scan a local documentation folder (e.g. Docusaurus, VitePress, Hugo, mkdocs) and sync all markdown files to Clusterify URL Knowledge in chunks:

```php
use Clusterify\Helpers\MarkdownSyncHelper;

$batches = MarkdownSyncHelper::syncDirectoryToUrls(
    client: $client,
    directoryPath: __DIR__ . '/docs',
    chatbotDomain: 'https://example.com',
    baseRoute: '/docs',
    chunkSize: 25
);

foreach ($batches as $batch) {
    echo "Batch processed: {$batch->processed} items | Errors: {$batch->errorsCount}\n";
}
```

---

### Configurable Retries with Jitter
Automatic retry handling for transient network blips and HTTP 429 rate limit responses:

```php
use Clusterify\Support\RetryPolicy;

$retryPolicy = new RetryPolicy(
    maxRetries: 3,        // Retry up to 3 times
    baseDelayMs: 500,     // Start with 500ms
    maxDelayMs: 10000,    // Cap at 10 seconds
    jitter: 0.25          // 25% randomized jitter
);
```

---

### Zero-Credential Secret Masking
`Credentials` instances automatically mask sensitive API secret keys when passed to `var_dump()`, `print_r()`, or captured in exception stack traces:

```php
$credentials = new \Clusterify\Auth\Credentials('pk_live_your_public_key_here', 'sk_live_your_secret_key_here');
var_dump($credentials);
// Output:
// class Clusterify\Auth\Credentials {
//   public string $publicKey => "pk_live_your_public_key_here"
//   public string $secretKey => "sk_live...here"
// }
```

---

## Framework Integration Recipes

### Laravel

Create a service provider or bind `ClusterifyClient` in `app/Providers/AppServiceProvider.php`:

```php
namespace App\Providers;

use Clusterify\ClusterifyClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ClusterifyClient::class, function () {
            return ClusterifyClient::builder()
                ->withCredentials(
                    publicKey: config('services.clusterify.public_key'),
                    secretKey: config('services.clusterify.secret_key')
                )
                ->build();
        });
    }
}
```

Add configuration to `config/services.php`:
```php
'clusterify' => [
    'public_key' => env('CLUSTERIFY_PUBLIC_KEY'),
    'secret_key' => env('CLUSTERIFY_SECRET_KEY'),
],
```

Inject anywhere:
```php
use Clusterify\ClusterifyClient;

class KnowledgeSyncController extends Controller
{
    public function sync(ClusterifyClient $clusterify)
    {
        $profile = $clusterify->profile()->get();
        return response()->json($profile->toArray());
    }
}
```

---

### Symfony

Configure the client in `config/services.yaml`:

```yaml
services:
    Clusterify\ClusterifyClient:
        factory: ['Clusterify\ClusterifyClient', 'create']
        arguments:
            $publicKey: '%env(CLUSTERIFY_PUBLIC_KEY)%'
            $secretKey: '%env(CLUSTERIFY_SECRET_KEY)%'
```

---

## Development & Testing

### Running Tests
```bash
composer test
# Or directly with PHPUnit:
vendor/bin/phpunit
```

### Static Analysis (PHPStan)
```bash
composer analyse
# Or directly:
vendor/bin/phpstan analyse
```

---

## License

The Clusterify PHP SDK is open-sourced software licensed under the **[MIT license](LICENSE)**.

---

## Support & Links

- **Clusterify Website**: [https://clusterify.ai](https://clusterify.ai)
- **API Documentation**: [https://dashboard.clusterify.ai/api-key](https://dashboard.clusterify.ai/api-key)
- **Support & Issues**: [support@clusterify.ai](mailto:support@clusterify.ai)
