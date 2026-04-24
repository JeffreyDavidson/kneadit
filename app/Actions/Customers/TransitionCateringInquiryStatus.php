<?php

namespace App\Actions\Customers;

use App\Enums\Customers\CateringInquiryStatus;
use App\Events\Marketing\CateringQuoteRequested;
use App\Models\Customers\CateringInquiry;

class TransitionCateringInquiryStatus
{
    public function __invoke(CateringInquiry $inquiry, CateringInquiryStatus $status): void
    {
        $inquiry->update(['status' => $status]);

        if ($status === CateringInquiryStatus::Quoted) {
            event(new CateringQuoteRequested($inquiry));
        }
    }
}
