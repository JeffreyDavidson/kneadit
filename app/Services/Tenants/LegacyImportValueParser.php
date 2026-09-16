<?php

namespace App\Services\Tenants;

use UnexpectedValueException;

final class LegacyImportValueParser
{
    public function string(mixed $value): string
    {
        if (! is_string($value) && ! is_int($value) && ! is_float($value)) {
            throw new UnexpectedValueException('Expected a string-compatible legacy value.');
        }

        return (string) $value;
    }

    public function integer(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (! is_string($value) || filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new UnexpectedValueException('Expected an integer-compatible legacy value.');
        }

        return (int) $value;
    }

    public function number(mixed $value): float
    {
        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }
        if (! is_string($value) || ! is_numeric($value)) {
            throw new UnexpectedValueException('Expected a numeric legacy value.');
        }

        return (float) $value;
    }

    public function cents(mixed $value): int
    {
        return (int) round($this->number($value) * 100);
    }
}
