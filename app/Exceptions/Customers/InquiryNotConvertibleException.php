<?php

namespace App\Exceptions\Customers;

use App\Models\Customers\CateringInquiry;
use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

class InquiryNotConvertibleException extends RuntimeException implements ShouldntReport
{
    public function __construct(
        public readonly CateringInquiry $inquiry,
        string $reason,
    ) {
        parent::__construct($reason);
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'inquiry_id' => $this->inquiry->id,
            'inquiry_status' => $this->inquiry->status->value,
        ];
    }
}
