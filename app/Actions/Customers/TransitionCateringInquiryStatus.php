<?php

declare(strict_types=1);

namespace App\Actions\Customers;

use App\Enums\Customers\CateringInquiryStatus;
use App\Models\Customers\CateringInquiry;

class TransitionCateringInquiryStatus
{
    public function __invoke(CateringInquiry $inquiry, CateringInquiryStatus $status): void
    {
        $inquiry->update(['status' => $status]);
    }
}
