<?php

namespace App\Actions\Customers;

use App\Models\CustomerNote;

class AddCustomerNote
{
    public function __invoke(int $customerId, string $note, int $createdBy): CustomerNote
    {
        return CustomerNote::query()->create([
            'customer_id' => $customerId,
            'note' => $note,
            'created_by' => $createdBy,
        ]);
    }
}
