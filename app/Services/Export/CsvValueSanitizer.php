<?php

declare(strict_types=1);

namespace App\Services\Export;

final class CsvValueSanitizer
{
    public static function sanitize(mixed $value): bool|float|int|string|null
    {
        if (! is_scalar($value) && $value !== null) {
            return '';
        }

        if (! is_string($value) || $value === '' || is_numeric($value)) {
            return $value;
        }

        // Excel and Sheets also run a formula that follows a leading tab or carriage return.
        return preg_match('/\A[=+\-@\t\r]/', $value) === 1 ? "'{$value}" : $value;
    }

    /**
     * Sanitizes every value of a nested array, leaving the keys alone.
     *
     * @param  array<array-key, mixed>  $values
     * @return array<array-key, mixed>
     */
    public static function deep(array $values): array
    {
        return array_map(
            fn (mixed $value): mixed => is_array($value) ? self::deep($value) : self::sanitize($value),
            $values,
        );
    }

    /**
     * @param  array<int, mixed>  $values
     * @return array<int, bool|float|int|string|null>
     */
    public static function row(array $values): array
    {
        return array_map(self::sanitize(...), $values);
    }
}
