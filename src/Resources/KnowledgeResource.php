<?php

declare(strict_types=1);

namespace Clusterify\Resources;

use Clusterify\DTO\Knowledge\CreateSectionRequest;
use Clusterify\DTO\Knowledge\KnowledgeListResponse;
use Clusterify\DTO\Knowledge\KnowledgeSection;
use Clusterify\DTO\Knowledge\KnowledgeSectionsResponse;
use Clusterify\DTO\Knowledge\UpdateSectionRequest;
use Clusterify\Exceptions\ClusterifyException;
use Clusterify\Exceptions\ResourceNotFoundException;
use Clusterify\Http\RequestOptions;
use Clusterify\Support\Payload;
use InvalidArgumentException;

/**
 * Simple Website Knowledge Base resource (knowledge_builder).
 */
final class KnowledgeResource extends AbstractResource
{
    /**
     * Retrieve all knowledge base sections along with active plan limits and usage.
     *
     * @param string|null $search Case-insensitive search query
     * @param RequestOptions|null $options
     * @return KnowledgeListResponse
     * @throws ClusterifyException
     */
    public function list(?string $search = null, ?RequestOptions $options = null): KnowledgeListResponse
    {
        $queryParams = [];
        if ($search !== null && trim($search) !== '') {
            $queryParams['search'] = $search;
        }

        $response = $this->http->get('/v1/dashboard/knowledge', $queryParams, $options);
        return KnowledgeListResponse::fromArray($response->payload);
    }

    /**
     * Fetch a specific knowledge section by its ID.
     *
     * @param string $id Target section ID (e.g. 'kb_1700000000_abcde')
     * @param RequestOptions|null $options
     * @return KnowledgeSection
     * @throws InvalidArgumentException
     * @throws ResourceNotFoundException
     * @throws ClusterifyException
     */
    public function get(string $id, ?RequestOptions $options = null): KnowledgeSection
    {
        if (trim($id) === '') {
            throw new InvalidArgumentException('Knowledge section ID must not be empty.');
        }

        $response = $this->http->get('/v1/dashboard/knowledge', ['id' => $id], $options);
        $list = KnowledgeListResponse::fromArray($response->payload);

        if ($list->sections !== []) {
            return $list->sections[0];
        }

        throw new ResourceNotFoundException("Knowledge section '{$id}' not found.");
    }

    /**
     * Create a new knowledge base section.
     *
     * @param CreateSectionRequest|string $title Section title or CreateSectionRequest DTO
     * @param string|null $content Section markdown content (required when $title is a string)
     * @param bool $isOpen Whether the section is active
     * @param RequestOptions|null $options
     * @return KnowledgeSection
     * @throws InvalidArgumentException
     * @throws ClusterifyException
     */
    public function create(
        CreateSectionRequest|string $title,
        ?string $content = null,
        bool $isOpen = true,
        ?RequestOptions $options = null
    ): KnowledgeSection {
        if ($title instanceof CreateSectionRequest) {
            $body = $title->toArray();
        } else {
            if ($content === null) {
                throw new InvalidArgumentException('Section content must be provided when creating a section by title.');
            }
            $body = [
                'title' => $title,
                'content' => $content,
                'is_open' => $isOpen,
            ];
        }

        $response = $this->http->post('/v1/dashboard/knowledge', $body, [], $options);
        return $this->extractSection($response->getData());
    }

    /**
     * Update an existing knowledge base section.
     *
     * @param UpdateSectionRequest|string $id Target section ID or UpdateSectionRequest DTO
     * @param string|null $title Updated section title
     * @param string|null $content Updated section content
     * @param bool|null $isOpen Updated active status
     * @param RequestOptions|null $options
     * @return KnowledgeSection
     * @throws InvalidArgumentException
     * @throws ClusterifyException
     */
    public function update(
        UpdateSectionRequest|string $id,
        ?string $title = null,
        ?string $content = null,
        ?bool $isOpen = null,
        ?RequestOptions $options = null
    ): KnowledgeSection {
        if ($id instanceof UpdateSectionRequest) {
            if (trim($id->id) === '') {
                throw new InvalidArgumentException('Knowledge section ID must not be empty.');
            }
            $body = $id->toArray();
        } else {
            if (trim($id) === '') {
                throw new InvalidArgumentException('Knowledge section ID must not be empty.');
            }
            $body = (new UpdateSectionRequest($id, $title, $content, $isOpen))->toArray();
        }

        $response = $this->http->post('/v1/dashboard/knowledge', $body, [], $options);
        return $this->extractSection($response->getData());
    }

    /**
     * Perform a targeted partial update on a knowledge section.
     *
     * @param string $id Target section ID
     * @param array{title?: string, content?: string, is_open?: bool, position?: int}|array<string, mixed> $attributes Fields to update
     * @param RequestOptions|null $options
     * @return KnowledgeSection
     * @throws InvalidArgumentException
     * @throws ClusterifyException
     */
    public function patch(string $id, array $attributes, ?RequestOptions $options = null): KnowledgeSection
    {
        if (trim($id) === '') {
            throw new InvalidArgumentException('Knowledge section ID must not be empty.');
        }

        if ($attributes === []) {
            throw new InvalidArgumentException('Patch attributes array must not be empty.');
        }

        $encodedId = rawurlencode($id);
        $response = $this->http->patch("/v1/dashboard/knowledge/{$encodedId}", $attributes, [], $options);
        return $this->extractSection($response->getData());
    }

    /**
     * Toggle the enabled/active status of a section.
     *
     * @param string $id Target section ID
     * @param bool $isOpen Whether the section is active
     * @param RequestOptions|null $options
     * @return KnowledgeSection
     * @throws InvalidArgumentException
     * @throws ClusterifyException
     */
    public function toggle(string $id, bool $isOpen, ?RequestOptions $options = null): KnowledgeSection
    {
        if (trim($id) === '') {
            throw new InvalidArgumentException('Knowledge section ID must not be empty.');
        }

        $body = [
            'action' => 'toggle',
            'id' => $id,
            'is_open' => $isOpen,
        ];

        $response = $this->http->post('/v1/dashboard/knowledge', $body, [], $options);
        return $this->extractSection($response->getData());
    }

    /**
     * Reorder the sequence of sections by providing an ordered list of section IDs.
     *
     * @param list<string> $orderedIds Array of section IDs in desired order
     * @param RequestOptions|null $options
     * @return KnowledgeSectionsResponse
     * @throws InvalidArgumentException
     * @throws ClusterifyException
     */
    public function reorder(array $orderedIds, ?RequestOptions $options = null): KnowledgeSectionsResponse
    {
        if ($orderedIds === []) {
            throw new InvalidArgumentException('orderedIds array must not be empty when reordering sections.');
        }

        $body = [
            'action' => 'reorder',
            'ordered_ids' => array_values($orderedIds),
        ];

        $response = $this->http->post('/v1/dashboard/knowledge', $body, [], $options);
        return KnowledgeSectionsResponse::fromArray($response->payload, $response->getMessage());
    }

    /**
     * Atomically replace all sections with the provided list.
     *
     * @param list<CreateSectionRequest|array<string, mixed>> $sections Array of section definitions
     * @param RequestOptions|null $options
     * @return KnowledgeSectionsResponse
     * @throws ClusterifyException
     */
    public function replaceAll(array $sections, ?RequestOptions $options = null): KnowledgeSectionsResponse
    {
        $normalized = array_values(array_map(
            static fn(CreateSectionRequest|array $sec): array => ($sec instanceof CreateSectionRequest) ? $sec->toArray() : $sec,
            $sections
        ));

        $body = ['sections' => $normalized];
        $response = $this->http->post('/v1/dashboard/knowledge', $body, [], $options);

        return KnowledgeSectionsResponse::fromArray($response->payload, $response->getMessage());
    }

    /**
     * Delete a single section by its ID.
     *
     * @param string $id Target section ID
     * @param RequestOptions|null $options
     * @return bool True if successfully deleted
     * @throws InvalidArgumentException
     * @throws ClusterifyException
     */
    public function delete(string $id, ?RequestOptions $options = null): bool
    {
        if (trim($id) === '') {
            throw new InvalidArgumentException('Knowledge section ID must not be empty.');
        }

        $encodedId = rawurlencode($id);
        $response = $this->http->delete("/v1/dashboard/knowledge/{$encodedId}", null, [], $options);
        return $response->isSuccess();
    }

    /**
     * Bulk delete multiple sections by their IDs.
     *
     * @param list<string> $ids Array of section IDs to delete
     * @param RequestOptions|null $options
     * @return bool True if successfully deleted
     * @throws InvalidArgumentException
     * @throws ClusterifyException
     */
    public function bulkDelete(array $ids, ?RequestOptions $options = null): bool
    {
        if ($ids === []) {
            throw new InvalidArgumentException('Section IDs array must not be empty for bulkDelete.');
        }

        $response = $this->http->delete('/v1/dashboard/knowledge', ['ids' => array_values($ids)], [], $options);
        return $response->isSuccess();
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractSection(array $data): KnowledgeSection
    {
        $sectionNode = array_key_exists('section', $data) && is_array($data['section'])
            ? Payload::map($data, 'section')
            : $data;

        return KnowledgeSection::fromArray($sectionNode);
    }
}
