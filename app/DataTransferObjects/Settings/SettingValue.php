<?php

namespace App\DataTransferObjects\Settings;

final class SettingValue
{
    public static function string(mixed $value, string $default = ''): string
    {
        return is_scalar($value) ? (string) $value : $default;
    }

    public static function nullableString(mixed $value): ?string
    {
        return is_scalar($value) ? (string) $value : null;
    }

    public static function int(mixed $value, int $default): int
    {
        return is_numeric($value) ? (int) $value : $default;
    }

    public static function nullableInt(mixed $value, ?int $default = null): ?int
    {
        return is_numeric($value) ? (int) $value : $default;
    }

    public static function bool(mixed $value, bool $default = false): bool
    {
        $filtered = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

        return is_bool($filtered) ? $filtered : $default;
    }

    public static function float(mixed $value, float $default): float
    {
        return is_numeric($value) ? (float) $value : $default;
    }

    /** @return array<mixed> */
    public static function decodedList(mixed $value): array
    {
        $decoded = json_decode(self::string($value, '[]'), true);

        return is_array($decoded) && array_is_list($decoded) ? $decoded : [];
    }

    /** @return array<string, mixed> */
    public static function decodedMap(mixed $value): array
    {
        $decoded = json_decode(self::string($value, '{}'), true);

        if (! is_array($decoded) || array_is_list($decoded)) {
            return [];
        }

        return self::map($decoded);
    }

    /** @return array<string, mixed> */
    public static function map(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $map = [];

        foreach ($value as $key => $item) {
            if (is_string($key)) {
                $map[$key] = $item;
            }
        }

        return $map;
    }

    /** @return list<string> */
    public static function stringList(mixed $value): array
    {
        return array_values(collect(self::decodedList($value))
            ->filter(fn (mixed $item): bool => is_string($item) && trim($item) !== '')
            ->all());
    }

    /** @return list<array<string, mixed>> */
    public static function mapList(mixed $value): array
    {
        return array_values(collect(self::decodedList($value))
            ->flatMap(fn (mixed $item): array => is_array($item) ? [self::map($item)] : [])
            ->all());
    }

    /** @return list<array<string, string>> */
    public static function stringMapList(mixed $value): array
    {
        return array_values(collect(self::mapList($value))
            ->map(function (array $item): array {
                return collect($item)
                    ->filter(fn (mixed $mapValue): bool => is_string($mapValue))
                    ->all();
            })
            ->filter(fn (array $item): bool => $item !== [])
            ->all());
    }
}
