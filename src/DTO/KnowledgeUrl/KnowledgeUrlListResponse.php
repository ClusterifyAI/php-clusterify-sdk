<?php

declare(strict_types=1);

namespace Clusterify\DTO\KnowledgeUrl;

use Clusterify\DTO\Common\Pagination;
use Clusterify\Support\Payload;
use JsonSerializable;

/**
 * URL knowledge base listing response envelope containing items, pagination, stats, and chatbot info.
 */
final readonly class KnowledgeUrlListResponse implements JsonSerializable
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
        $dataNode = Payload::unwrapData($data);
        $rawItems = Payload::listOfMaps($dataNode, 'items');
        $items = array_map(
            static fn(array $item): KnowledgeUrlItem => KnowledgeUrlItem::fromArray($item),
            $rawItems
        );

        $paginationNode = Payload::map($dataNode, 'pagination');
        $pagination = Pagination::fromArray($paginationNode);

        $statsNode = Payload::map($dataNode, 'stats');
        $stats = KnowledgeUrlStats::fromArray($statsNode);

        $chatbotNode = Payload::map($dataNode, 'chatbot');
        $chatbot = KnowledgeUrlChatbot::fromArray($chatbotNode);

        return new self(
            success: Payload::bool($data, 'success', true),
            items: $items,
            pagination: $pagination,
            stats: $stats,
            chatbot: $chatbot
        );
    }

    /**
     * @return array{
     *     success: bool,
     *     data: array{
     *         items: list<array<string, mixed>>,
     *         pagination: array{page: int, limit: int, total_items: int, total_pages: int},
     *         stats: array<string, int>,
     *         chatbot: array{id: int, domain: string}
     *     }
     * }
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

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
