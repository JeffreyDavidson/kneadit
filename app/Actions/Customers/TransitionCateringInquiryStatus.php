<?php

namespace App\Actions\Customers;

use App\Enums\CateringInquiryStatus;
use App\Events\Marketing\CateringQuoteRequested;
use App\Models\CateringInquiry;

class TransitionCateringInquiryStatus
{
    public function __invoke(CateringInquiry $inquiry, CateringInquiryStatus $status): void
    {
        $inquiry->update(['status' => $status]);

        if ($status === CateringInquiryStatus::Quoted) {
            CateringQuoteRequested::dispatch($inquiry);
        }
    }
}
