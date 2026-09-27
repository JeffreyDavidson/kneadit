<?php

declare(strict_types=1);

namespace App\Services\Customers;

use App\Models\Customers\CateringInquiry;

class CateringDepositCalculator
{
    public function suggestedAmount(CateringInquiry $inquiry, int $depositPercent): float
    {
        if (! $inquiry->quoted_amount || $depositPercent <= 0) {
            return 0.0;
        }

        return round($inquiry->quoted_amount->dollars() * (min(100, $depositPercent) / 100), 2);
    }
}
