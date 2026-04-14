<?php

namespace App\Support;

class ProfitMargin
{
    public static function calculate(float $price, float $cost, int $precision = 2): ?float
    {
        if ($price <= 0) {
            return null;
        }

        return round(($price - $cost) / $price * 100, $precision);
    }
}
