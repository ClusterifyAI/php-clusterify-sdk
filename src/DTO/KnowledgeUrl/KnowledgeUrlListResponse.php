<?php

declare(strict_types=1);

namespace Clusterify\DTO\KnowledgeUrl;

use Clusterify\DTO\Common\Pagination;

/**
 * URL knowledge base listing response envelope containing items, pagination, stats, and chatbot info.
 */
final readonly class KnowledgeUrlListResponse
{
    /**
     * @param bool $success Whether the request succeeded
     * @param list<KnowledgeUrlItem> $items List of URL knowledge entries
     * @param Pagination $pagination Pagination metadata
     * @param KnowledgeUrlStats $stats Aggregate capacity and count statistics
     * @param KnowledgeUrlChatbot $chatbot Chatbot reference information
     */
    public function __construct(
        public bool $success,
        public array $items,
        public Pagination $pagination,
        public KnowledgeUrlStats $stats,
        public KnowledgeUrlChatbot $chatbot
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed> $dataNode */
        $dataNode = $data['data'] ?? $data;
        /** @var list<array<string, mixed>> $rawItems */
        $rawItems = $dataNode['items'] ?? [];
        $items = array_values(array_map(
            static fn(array $item): KnowledgeUrlItem => KnowledgeUrlItem::fromArray($item),
            $rawItems
        ));

        /** @var array<string, mixed> $paginationNode */
        $paginationNode = $dataNode['pagination'] ?? [];
        $pagination = Pagination::fromArray($paginationNode);

        /** @var array<string, mixed> $statsNode */
        $statsNode = $dataNode['stats'] ?? [];
        $stats = KnowledgeUrlStats::fromArray($statsNode);

        /** @var array<string, mixed> $chatbotNode */
        $chatbotNode = $dataNode['chatbot'] ?? [];
        $chatbot = KnowledgeUrlChatbot::fromArray($chatbotNode);

        return new self(
            success: (bool) ($data['success'] ?? true),
            items: $items,
            pagination: $pagination,
            stats: $stats,
            chatbot: $chatbot
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => [
                'items' => array_map(static fn(KnowledgeUrlItem $item): array => $item->toArray(), $this->items),
                'pagination' => $this->pagination->toArray(),
                'stats' => $this->stats->toArray(),
                'chatbot' => $this->chatbot->toArray(),
            ],
        ];
    }
}
