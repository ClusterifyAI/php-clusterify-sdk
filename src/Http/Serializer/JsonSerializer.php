<?php

declare(strict_types=1);

namespace Clusterify\Http\Serializer;

use Clusterify\Exceptions\ClusterifyException;
use JsonException;

/**
 * Robust JSON serializer with strict error handling.
 */
final class JsonSerializer
{
    /**
     * Encode PHP data structure to JSON string.
     *
     * @param mixed $data
     * @return string
     * @throws ClusterifyException
     */
    public static function encode(mixed $data): string
    {
        try {
            return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $e) {
            throw new ClusterifyException('Failed to JSON-encode request payload: ' . $e->getMessage(), 'JSON_ENCODE_ERROR', null, [], $e);
        }
    }

    /**
     * Decode JSON string to associative array.
     *
     * @param string $json
     * @return array<string, mixed>
     * @throws ClusterifyException
     */
    public static function decode(string $json): array
    {
        if (trim($json) === '') {
            return [];
        }

        try {
            /** @var array<string, mixed> $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            return $decoded;
        } catch (JsonException $e) {
            throw new ClusterifyException('Failed to JSON-decode response body: ' . $e->getMessage(), 'JSON_DECODE_ERROR', null, [], $e);
        }
    }
}
