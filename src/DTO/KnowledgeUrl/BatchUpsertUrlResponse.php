<?php

declare(strict_types=1);

namespace Clusterify\DTO\KnowledgeUrl;

use Clusterify\Support\Payload;
use JsonSerializable;

/**
 * Dedicated response envelope for batch URL knowledge upsert operations.
 */
final readonly class BatchUpsertUrlResponse implements JsonSerializable
{
    /**
     * @param bool $success Whether the request succeeded overall
     * @param int $processed Count of successfully processed items
     * @param int $errorsCount Count of failed items
     * @param list<BatchUpsertResultItem> $results Successfully created/updated entries
     * @param list<BatchUpsertErrorItem> $errors List of validation or processing failures
     * @param string|null $message API message
     */
    public function __construct(
        public bool $success,
        public int $processed,
        public int $errorsCount,
        public array $results,
        public array $errors = [],
        public ?string $message = null
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $dataNode = Payload::unwrapData($data);

        $rawResults = Payload::listOfMaps($dataNode, 'results');
        $results = array_map(
            static fn(array $res): BatchUpsertResultItem => BatchUpsertResultItem::fromArray($res),
            $rawResults
        );

        $rawErrors = Payload::listOfMaps($dataNode, 'errors');
        $errors = array_map(
            static fn(array $err): BatchUpsertErrorItem => BatchUpsertErrorItem::fromArray($err),
            $rawErrors
        );

        $msg = Payload::nullableString($data, 'message');

        return new self(
            success: Payload::bool($data, 'success', true),
            processed: Payload::nullableInt($dataNode, 'processed') ?? count($results),
            errorsCount: Payload::nullableInt($dataNode, 'errors_count') ?? count($errors),
            results: $results,
            errors: $errors,
            message: $msg
        );
    }

    /**
     * @return array{
     *     success: bool,
     *     message: string|null,
     *     data: array{
     *         processed: int,
     *         errors_count: int,
     *         results: list<array<string, mixed>>,
     *         errors: list<array{index: int, url: string, error: string}>
     *     }
     * }
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data' => [
                'processed' => $this->processed,
                'errors_count' => $this->errorsCount,
                'results' => array_map(static fn(BatchUpsertResultItem $r): array => $r->toArray(), $this->results),
                'errors' => array_map(static fn(BatchUpsertErrorItem $e): array => $e->toArray(), $this->errors),
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
