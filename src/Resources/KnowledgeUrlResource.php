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
use Clusterify\Http\RequestOptions;
use Generator;

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

        if (!empty($list->items)) {
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
     * @throws ResourceNotFoundException
     * @throws ClusterifyException
     */
    public function getByUrl(string $url, ?RequestOptions $options = null): KnowledgeUrlItem
    {
        $response = $this->http->get('/v1/dashboard/knowledge-url', ['url' => $url], $options);
        $list = KnowledgeUrlListResponse::fromArray($response->payload);

        if (!empty($list->items)) {
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
     * @param string|null $content Markdown documentation content
     * @param bool $isEnabled Whether the URL knowledge is enabled
     * @param RequestOptions|null $options
     * @return KnowledgeUrlItem
     * @throws ClusterifyException
     */
    public function upsert(
        UpsertUrlRequest|string $url,
        ?string $content = null,
        bool $isEnabled = true,
        ?RequestOptions $options = null
    ): KnowledgeUrlItem {
        $body = ($url instanceof UpsertUrlRequest)
            ? $url->toArray()
            : [
                'url' => $url,
                'content' => (string) $content,
                'is_enabled' => $isEnabled,
            ];

        $response = $this->http->post('/v1/dashboard/knowledge-url', $body, [], $options);
        $data = $response->getData();
        /** @var array<string, mixed> $itemNode */
        $itemNode = $data['item'] ?? $data;

        return KnowledgeUrlItem::fromArray($itemNode);
    }

    /**
     * Toggle the enabled/disabled status of a URL knowledge entry.
     *
     * @param string|int $urlOrId Target URL string or record integer ID
     * @param bool $isEnabled Target active status
     * @param RequestOptions|null $options
     * @return KnowledgeUrlItem
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
            $body['url'] = $urlOrId;
        }

        $response = $this->http->post('/v1/dashboard/knowledge-url', $body, [], $options);
        $data = $response->getData();
        /** @var array<string, mixed> $itemNode */
        $itemNode = $data['item'] ?? $data;

        return KnowledgeUrlItem::fromArray($itemNode);
    }

    /**
     * Batch upsert multiple URL knowledge entries atomically or with partial results.
     *
     * @param BatchUpsertUrlRequest|list<UpsertUrlRequest|array<string, mixed>> $items
     * @param RequestOptions|null $options
     * @return BatchUpsertUrlResponse
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
     * @param array<string, mixed> $attributes Fields to update (e.g. ['is_enabled' => false])
     * @param RequestOptions|null $options
     * @return KnowledgeUrlItem
     * @throws ClusterifyException
     */
    public function patch(int $id, array $attributes, ?RequestOptions $options = null): KnowledgeUrlItem
    {
        $response = $this->http->patch("/v1/dashboard/knowledge-url/{$id}", $attributes, [], $options);
        $data = $response->getData();
        /** @var array<string, mixed> $itemNode */
        $itemNode = $data['item'] ?? $data;

        return KnowledgeUrlItem::fromArray($itemNode);
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
     * @throws ClusterifyException
     */
    public function bulkDelete(array $ids = [], array $urls = [], ?RequestOptions $options = null): bool
    {
        $body = [];
        if (!empty($ids)) {
            $body['ids'] = $ids;
        }
        if (!empty($urls)) {
            $body['urls'] = $urls;
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
        $page = 1;
        $limit = 50;

        $queryParams = ($query instanceof KnowledgeUrlQuery)
            ? $query->toQueryParams()
            : $query;

        if (isset($queryParams['limit']) && is_numeric($queryParams['limit'])) {
            $limit = (int) $queryParams['limit'];
        }

        while (true) {
            $currentQuery = array_merge($queryParams, [
                'page' => $page,
                'limit' => $limit,
            ]);

            $response = $this->list($currentQuery, $options);

            foreach ($response->items as $item) {
                yield $item;
            }

            if ($page >= $response->pagination->totalPages || empty($response->items)) {
                break;
            }

            $page++;
        }
    }
}
