<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Formats a stock or recipe quantity, which is stored to 4 decimals (1 g of stock held in kg is 0.001).
 */
final class StockQuantity
{
    /**
     * For reading: 2 decimals, unless that would show a small non-zero quantity as 0.00, in which
     * case the stored digits are kept (0.003).
     */
    public static function display(float|int|string|null $value): string
    {
        $number = (float) $value;
        $rounded = number_format($number, 2, '.', '');

        if ((float) $rounded !== 0.0 || $number === 0.0) {
            return $rounded;
        }

        return self::trimmed($number);
    }

    /**
     * For editing: at least 2 decimals, and never fewer than the stored digits so a save can't round a value away.
     */
    public static function input(float|int|string|null $value): string
    {
        $trimmed = self::trimmed((float) $value);

        return str_contains($trimmed, '.') ? str_pad($trimmed, strpos($trimmed, '.') + 3, '0') : "{$trimmed}.00";
    }

    private static function trimmed(float $number): string
    {
        return rtrim(rtrim(number_format($number, 4, '.', ''), '0'), '.');
    }
}
