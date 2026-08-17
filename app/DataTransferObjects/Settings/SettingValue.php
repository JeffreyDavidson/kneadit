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
}
