<?php

declare(strict_types=1);

namespace Clusterify\Resources;

use Clusterify\DTO\KnowledgeUrl\BatchUpsertUrlRequest;
use Clusterify\DTO\KnowledgeUrl\BatchUpsertUrlResponse;
use Clusterify\DTO\KnowledgeUrl\KnowledgeUrlItem;
use Clusterify\DTO\KnowledgeUrl\KnowledgeUrlListResponse;
use Clusterify\DTO\KnowledgeUrl\KnowledgeUrlQuery;
use Clusterify\DTO\KnowledgeUrl\KnowledgeUrlStats;
use Clusterify\DTO\KnowledgeUrl\UpsertUrlRequest;
use Clusterify\Exceptions\ClusterifyException;
use Clusterify\Exceptions\ResourceNotFoundException;
use Clusterify\Helpers\AutoPaginator;
use Clusterify\Http\RequestOptions;
use Clusterify\Support\Payload;
use Generator;
use InvalidArgumentException;

/**
 * URL-Based Knowledge Base resource (knowledge_url) — Professional Plan.
 */
final class KnowledgeUrlResource extends AbstractResource
{
    /**
     * List URL knowledge entries with optional query filters and pagination.
     *
     * @param KnowledgeUrlQuery|array<string, mixed> $query Filter/pagination query
     * @param RequestOptions|null $options
     * @return KnowledgeUrlListResponse
     * @throws ClusterifyException
     */
    public function list(KnowledgeUrlQuery|array $query = [], ?RequestOptions $options = null): KnowledgeUrlListResponse
    {
        $queryParams = ($query instanceof KnowledgeUrlQuery) ? $query->toQueryParams() : $query;
        $response = $this->http->get('/v1/dashboard/knowledge-url', $queryParams, $options);
        return KnowledgeUrlListResponse::fromArray($response->payload);
    }

    /**
     * Fetch a single URL knowledge entry by its record ID.
     *
     * @param int $id Target record ID
     * @param RequestOptions|null $options
     * @return KnowledgeUrlItem
     * @throws ResourceNotFoundException
     * @throws ClusterifyException
     */
    public function get(int $id, ?RequestOptions $options = null): KnowledgeUrlItem
    {
        $response = $this->http->get('/v1/dashboard/knowledge-url', ['id' => $id], $options);
        $list = KnowledgeUrlListResponse::fromArray($response->payload);

        if ($list->items !== []) {
            return $list->items[0];
        }

        throw new ResourceNotFoundException("Knowledge URL record with ID {$id} not found.");
    }

    /**
     * Fetch a single URL knowledge entry by exact URL.
     *
     * @param string $url Registered webpage URL
     * @param RequestOptions|null $options
     * @return KnowledgeUrlItem
     * @throws InvalidArgumentException
     * @throws ResourceNotFoundException
     * @throws ClusterifyException
     */
    public function getByUrl(string $url, ?RequestOptions $options = null): KnowledgeUrlItem
    {
        if (trim($url) === '') {
            throw new InvalidArgumentException('Knowledge URL must not be empty.');
        }

        $response = $this->http->get('/v1/dashboard/knowledge-url', ['url' => $url], $options);
        $list = KnowledgeUrlListResponse::fromArray($response->payload);

        if ($list->items !== []) {
            return $list->items[0];
        }

        throw new ResourceNotFoundException("Knowledge URL entry for '{$url}' not found.");
    }

    /**
     * Retrieve aggregate capacity and usage statistics for URL knowledge.
     *
     * @param RequestOptions|null $options
     * @return KnowledgeUrlStats
     * @throws ClusterifyException
     */
    public function stats(?RequestOptions $options = null): KnowledgeUrlStats
    {
        $response = $this->http->get('/v1/dashboard/knowledge-url', ['stats' => 'true'], $options);
        $list = KnowledgeUrlListResponse::fromArray($response->payload);
        return $list->stats;
    }

    /**
     * Smart Upsert: Create or update a URL knowledge entry.
     *
     * @param UpsertUrlRequest|string $url URL or UpsertUrlRequest DTO
     * @param string|null $content Markdown documentation content (required when $url is a string)
     * @param bool $isEnabled Whether the URL knowledge is enabled
     * @param RequestOptions|null $options
     * @return KnowledgeUrlItem
     * @throws InvalidArgumentException
     * @throws ClusterifyException
     */
    public function upsert(
        UpsertUrlRequest|string $url,
        ?string $content = null,
        bool $isEnabled = true,
        ?RequestOptions $options = null
    ): KnowledgeUrlItem {
        if ($url instanceof UpsertUrlRequest) {
            $body = $url->toArray();
        } else {
            if (trim($url) === '') {
                throw new InvalidArgumentException('Knowledge URL must not be empty.');
            }
            if ($content === null) {
                throw new InvalidArgumentException('Markdown content must be provided when upserting by URL string.');
            }
            $body = [
                'url' => $url,
                'content' => $content,
                'is_enabled' => $isEnabled,
            ];
        }

        $response = $this->http->post('/v1/dashboard/knowledge-url', $body, [], $options);
        return $this->extractItem($response->getData());
    }

    /**
     * Edit an existing URL knowledge record by its integer ID via the unified POST endpoint.
     *
     * @param int $id Target record ID
     * @param string|null $url Optional updated webpage URL
     * @param string|null $content Optional updated markdown content
     * @param bool|null $isEnabled Optional updated enabled state
     * @param RequestOptions|null $options
     * @return KnowledgeUrlItem
     * @throws InvalidArgumentException
     * @throws ClusterifyException
     */
    public function update(
        int $id,
        ?string $url = null,
        ?string $content = null,
        ?bool $isEnabled = null,
        ?RequestOptions $options = null
    ): KnowledgeUrlItem {
        if ($id <= 0) {
            throw new InvalidArgumentException('Knowledge URL record ID must be a positive integer.');
        }

        $body = ['id' => $id];
        if ($url !== null) {
            $body['url'] = $url;
        }
        if ($content !== null) {
            $body['content'] = $content;
        }
        if ($isEnabled !== null) {
            $body['is_enabled'] = $isEnabled;
        }

        if (count($body) === 1) {
            throw new InvalidArgumentException('At least one field (url, content, or isEnabled) must be provided to update.');
        }

        $response = $this->http->post('/v1/dashboard/knowledge-url', $body, [], $options);
        return $this->extractItem($response->getData());
    }

    /**
     * Toggle the enabled/disabled status of a URL knowledge entry.
     *
     * @param string|int $urlOrId Target URL string or record integer ID
     * @param bool $isEnabled Target active status
     * @param RequestOptions|null $options
     * @return KnowledgeUrlItem
     * @throws InvalidArgumentException
     * @throws ClusterifyException
     */
    public function toggle(string|int $urlOrId, bool $isEnabled, ?RequestOptions $options = null): KnowledgeUrlItem
    {
        $body = [
            'action' => 'toggle',
            'is_enabled' => $isEnabled,
        ];

        if (is_int($urlOrId)) {
            $body['id'] = $urlOrId;
        } else {
            if (trim($urlOrId) === '') {
                throw new InvalidArgumentException('Target URL must not be empty when toggling status.');
            }
            $body['url'] = $urlOrId;
        }

        $response = $this->http->post('/v1/dashboard/knowledge-url', $body, [], $options);
        return $this->extractItem($response->getData());
    }

    /**
     * Batch upsert multiple URL knowledge entries atomically or with partial results.
     *
     * @param BatchUpsertUrlRequest|list<UpsertUrlRequest|array<string, mixed>> $items
     * @param RequestOptions|null $options
     * @return BatchUpsertUrlResponse
     * @throws InvalidArgumentException
     * @throws ClusterifyException
     */
    public function batchUpsert(
        BatchUpsertUrlRequest|array $items,
        ?RequestOptions $options = null
    ): BatchUpsertUrlResponse {
        $body = ($items instanceof BatchUpsertUrlRequest)
            ? $items->toArray()
            : BatchUpsertUrlRequest::fromList($items)->toArray();

        $response = $this->http->post('/v1/dashboard/knowledge-url', $body, [], $options);
        return BatchUpsertUrlResponse::fromArray($response->payload);
    }

    /**
     * Perform a targeted partial update on a URL knowledge record.
     *
     * @param int $id Target record ID
     * @param array{url?: string, content?: string, is_enabled?: bool}|array<string, mixed> $attributes Fields to update
     * @param RequestOptions|null $options
     * @return KnowledgeUrlItem
     * @throws InvalidArgumentException
     * @throws ClusterifyException
     */
    public function patch(int $id, array $attributes, ?RequestOptions $options = null): KnowledgeUrlItem
    {
        if ($attributes === []) {
            throw new InvalidArgumentException('Patch attributes array must not be empty.');
        }

        $response = $this->http->patch("/v1/dashboard/knowledge-url/{$id}", $attributes, [], $options);
        return $this->extractItem($response->getData());
    }

    /**
     * Delete a single URL knowledge record by its ID.
     *
     * @param int $id Target record ID
     * @param RequestOptions|null $options
     * @return bool True if successfully deleted
     * @throws ClusterifyException
     */
    public function delete(int $id, ?RequestOptions $options = null): bool
    {
        $response = $this->http->delete("/v1/dashboard/knowledge-url/{$id}", null, [], $options);
        return $response->isSuccess();
    }

    /**
     * Bulk delete multiple URL knowledge records by IDs and/or exact URLs.
     *
     * @param list<int> $ids Array of record IDs to delete
     * @param list<string> $urls Array of URLs to delete
     * @param RequestOptions|null $options
     * @return bool True if successfully deleted
     * @throws InvalidArgumentException
     * @throws ClusterifyException
     */
    public function bulkDelete(array $ids = [], array $urls = [], ?RequestOptions $options = null): bool
    {
        if ($ids === [] && $urls === []) {
            throw new InvalidArgumentException('At least one ID or URL must be provided for bulkDelete.');
        }

        $body = [];
        if ($ids !== []) {
            $body['ids'] = array_values($ids);
        }
        if ($urls !== []) {
            $body['urls'] = array_values($urls);
        }

        $response = $this->http->delete('/v1/dashboard/knowledge-url', $body, [], $options);
        return $response->isSuccess();
    }

    /**
     * Lazy generator that automatically handles pagination across all URL knowledge records.
     * Memory-efficient for processing large datasets in CLI commands or background sync daemons.
     *
     * @param KnowledgeUrlQuery|array<string, mixed> $query Base query parameters
     * @param RequestOptions|null $options
     * @return Generator<int, KnowledgeUrlItem>
     * @throws ClusterifyException
     */
    public function autoPaginate(
        KnowledgeUrlQuery|array $query = [],
        ?RequestOptions $options = null
    ): Generator {
        $queryParams = ($query instanceof KnowledgeUrlQuery)
            ? $query->toQueryParams()
            : $query;

        $limit = isset($queryParams['limit']) && is_numeric($queryParams['limit'])
            ? max(1, min(100, (int) $queryParams['limit']))
            : 50;

        return AutoPaginator::iterate(function (int $page) use ($queryParams, $limit, $options): array {
            $currentQuery = array_merge($queryParams, [
                'page' => $page,
                'limit' => $limit,
            ]);

            $response = $this->list($currentQuery, $options);

            return [
                'items' => $response->items,
                'total_pages' => $response->pagination->totalPages,
            ];
        });
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractItem(array $data): KnowledgeUrlItem
    {
        $itemNode = array_key_exists('item', $data) && is_array($data['item'])
            ? Payload::map($data, 'item')
            : $data;

        return KnowledgeUrlItem::fromArray($itemNode);
    }
}
