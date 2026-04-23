<?php

namespace App\Actions\Customers;

use App\Events\Marketing\CateringInquiryReceived;
use App\Models\Customers\CateringInquiry;

class CreateCateringInquiry
{
    /** @param array<string, mixed> $data */
    public function __invoke(array $data): CateringInquiry
    {
        $inquiry = CateringInquiry::query()->create($data);

        event(new CateringInquiryReceived($inquiry));

        return $inquiry;
    }
}
