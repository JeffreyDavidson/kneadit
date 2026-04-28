<?php

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
