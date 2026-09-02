<?php

declare(strict_types=1);

namespace Clusterify\DTO\KnowledgeUrl;

/**
 * Fluent query parameters builder for filtering and paginating URL knowledge records.
 */
final class KnowledgeUrlQuery
{
    private ?int $page = null;
    private ?int $limit = null;
    private ?string $status = null;
    private ?string $search = null;
    private ?string $url = null;
    private ?int $id = null;
    private ?bool $stats = null;

    public static function create(): self
    {
        return new self();
    }

    /**
     * @param int $page 1-based page number
     */
    public function setPage(int $page): self
    {
        $this->page = max(1, $page);
        return $this;
    }

    /**
     * @param int $limit Items per page (max 100)
     */
    public function setLimit(int $limit): self
    {
        $this->limit = max(1, min(100, $limit));
        return $this;
    }

    /**
     * @param string $status Filter by 'all', 'enabled', or 'disabled'
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param string $search Search query across URLs
     */
    public function setSearch(string $search): self
    {
        $this->search = $search;
        return $this;
    }

    /**
     * @param string $url Exact URL lookup
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @param int $id Exact record ID lookup
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param bool $stats Whether to return aggregate statistics only
     */
    public function setStats(bool $stats = true): self
    {
        $this->stats = $stats;
        return $this;
    }

    /**
     * Convert query parameters into an associative array for HTTP requests.
     *
     * @return array<string, mixed>
     */
    public function toQueryParams(): array
    {
        $params = [];

        if ($this->page !== null) {
            $params['page'] = $this->page;
        }

        if ($this->limit !== null) {
            $params['limit'] = $this->limit;
        }

        if ($this->status !== null) {
            $params['status'] = $this->status;
        }

        if ($this->search !== null) {
            $params['search'] = $this->search;
        }

        if ($this->url !== null) {
            $params['url'] = $this->url;
        }

        if ($this->id !== null) {
            $params['id'] = $this->id;
        }

        if ($this->stats !== null) {
            $params['stats'] = $this->stats ? 'true' : 'false';
        }

        return $params;
    }
}
