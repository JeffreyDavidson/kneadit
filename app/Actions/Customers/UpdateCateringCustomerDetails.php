<?php

namespace App\Actions\Customers;

use App\Models\Customers\CateringInquiry;

class UpdateCateringCustomerDetails
{
    public function __invoke(
        CateringInquiry $inquiry,
        string $name,
        string $email,
        ?string $phone,
    ): void {
        $inquiry->update([
            'customer_name' => $name,
            'customer_email' => $email,
            'customer_phone' => $phone,
        ]);
    }
}
