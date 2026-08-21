<?php

namespace App\Actions\Customers;

use App\Models\Customers\CateringInquiry;

class UpdateCateringInquiryNotes
{
    public function __invoke(CateringInquiry $inquiry, ?string $notes): void
    {
        $inquiry->update(['notes' => $notes]);
    }
}
