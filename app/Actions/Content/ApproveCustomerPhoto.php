<?php

namespace App\Actions\Content;

use App\Models\CustomerPhoto;

class ApproveCustomerPhoto
{
    public function __invoke(CustomerPhoto $photo): void
    {
        $photo->update(['is_approved' => true]);
    }
}
