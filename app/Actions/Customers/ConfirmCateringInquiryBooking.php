<?php

namespace App\Actions\Customers;

use App\Models\Customers\CateringInquiry;
use App\Models\Orders\Order;

class ConfirmCateringInquiryBooking
{
    public function __construct(
        private readonly ConvertCateringInquiryToOrder $convertInquiry,
    ) {}

    public function __invoke(CateringInquiry $inquiry): Order
    {
        return ($this->convertInquiry)($inquiry);
    }
}
