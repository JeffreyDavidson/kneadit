<?php

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

        return preg_match('/\A[=+\-@]/', $value) === 1 ? "'{$value}" : $value;
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
