<?php

declare(strict_types=1);

namespace App\Actions\Customers;

use App\Events\Marketing\CateringQuoteRequested;
use App\Models\Customers\CateringInquiry;

class ResendCateringQuote
{
    public function __invoke(CateringInquiry $inquiry): void
    {
        event(new CateringQuoteRequested($inquiry));
    }
}
