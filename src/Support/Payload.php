<?php

declare(strict_types=1);

namespace Clusterify\Support;

/**
 * Type-safe runtime extractor for untrusted JSON / associative array payloads.
 *
 * @internal
 */
final class Payload
{
    /**
     * Extract a string value safely without triggering array-to-string warnings.
     *
     * @param array<string, mixed> $data
     */
    public static function string(array $data, string $key, string $default = ''): string
    {
        if (!array_key_exists($key, $data)) {
            return $default;
        }

        $value = $data[$key];
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value) || is_bool($value)) {
            return (string) $value;
        }

        return $default;
    }

    /**
     * Extract a nullable string value safely.
     *
     * @param array<string, mixed> $data
     */
    public static function nullableString(array $data, string $key): ?string
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }

        $value = $data[$key];
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return null;
    }

    /**
     * Extract an integer value safely without triggering object/array cast errors.
     *
     * @param array<string, mixed> $data
     */
    public static function int(array $data, string $key, int $default = 0): int
    {
        if (!array_key_exists($key, $data)) {
            return $default;
        }

        $value = $data[$key];
        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return $default;
    }

    /**
     * Extract a nullable integer value safely.
     *
     * @param array<string, mixed> $data
     */
    public static function nullableInt(array $data, string $key): ?int
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }

        $value = $data[$key];
        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    /**
     * Extract a boolean value safely.
     *
     * @param array<string, mixed> $data
     */
    public static function bool(array $data, string $key, bool $default = false): bool
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return $default;
        }

        $value = $data[$key];
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value !== 0;
        }

        if (is_string($value)) {
            $parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            return $parsed ?? $default;
        }

        return $default;
    }

    /**
     * Extract an associative array (`array<string, mixed>`) safely.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function map(array $data, string $key): array
    {
        if (!array_key_exists($key, $data) || !is_array($data[$key])) {
            return [];
        }

        /** @var array<string, mixed> $result */
        $result = $data[$key];
        return $result;
    }

    /**
     * Unwrap `data` envelope node if it is an associative array, otherwise return `$data` itself.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function unwrapData(array $data): array
    {
        if (array_key_exists('data', $data) && is_array($data['data'])) {
            /** @var array<string, mixed> $unwrapped */
            $unwrapped = $data['data'];
            return $unwrapped;
        }

        return $data;
    }

    /**
     * Extract a list of associative arrays safely, skipping non-array elements.
     *
     * @param array<string, mixed> $data
     * @return list<array<string, mixed>>
     */
    public static function listOfMaps(array $data, string $key): array
    {
        if (!array_key_exists($key, $data) || !is_array($data[$key])) {
            return [];
        }

        $items = [];
        foreach ($data[$key] as $element) {
            if (is_array($element)) {
                /** @var array<string, mixed> $element */
                $items[] = $element;
            }
        }

        return $items;
    }

    /**
     * Extract a list of strings safely, skipping non-scalar elements.
     *
     * @param array<string, mixed> $data
     * @return list<string>
     */
    public static function listOfStrings(array $data, string $key): array
    {
        if (!array_key_exists($key, $data) || !is_array($data[$key])) {
            return [];
        }

        $items = [];
        foreach ($data[$key] as $element) {
            if (is_string($element)) {
                $items[] = $element;
            } elseif (is_int($element) || is_float($element) || is_bool($element)) {
                $items[] = (string) $element;
            }
        }

        return $items;
    }
}

