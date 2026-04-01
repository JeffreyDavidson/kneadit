<?php

namespace App\Actions\Customers;

use App\Models\Customers\CateringInquiry;

class CreateCateringInquiry
{
    /** @param array<string, mixed> $data */
    public function __invoke(array $data): CateringInquiry
    {
        return CateringInquiry::query()->create($data);
    }
}
