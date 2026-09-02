<?php

declare(strict_types=1);

namespace Clusterify\DTO\KnowledgeUrl;

/**
 * Dedicated response envelope for batch URL knowledge upsert operations.
 */
final readonly class BatchUpsertUrlResponse
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
        /** @var array<string, mixed> $dataNode */
        $dataNode = $data['data'] ?? $data;

        /** @var list<array<string, mixed>> $rawResults */
        $rawResults = $dataNode['results'] ?? [];
        $results = array_values(array_map(
            static fn(array $res): BatchUpsertResultItem => BatchUpsertResultItem::fromArray($res),
            $rawResults
        ));

        /** @var list<array<string, mixed>> $rawErrors */
        $rawErrors = $dataNode['errors'] ?? [];
        $errors = array_values(array_map(
            static fn(array $err): BatchUpsertErrorItem => BatchUpsertErrorItem::fromArray($err),
            $rawErrors
        ));

        $msg = isset($data['message']) && is_string($data['message']) ? $data['message'] : null;

        return new self(
            success: (bool) ($data['success'] ?? true),
            processed: (int) ($dataNode['processed'] ?? count($results)),
            errorsCount: (int) ($dataNode['errors_count'] ?? count($errors)),
            results: $results,
            errors: $errors,
            message: $msg
        );
    }

    /**
     * @return array<string, mixed>
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
}
