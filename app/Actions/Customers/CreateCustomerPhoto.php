<?php

namespace App\Actions\Customers;

use App\Models\CustomerPhoto;
use Illuminate\Http\UploadedFile;

class CreateCustomerPhoto
{
    public function __invoke(
        UploadedFile $photo,
        string $customerName,
        string $customerEmail,
        ?string $caption = null,
        ?int $productId = null,
    ): CustomerPhoto {
        $path = $photo->store('customer-photos', 'public');

        return CustomerPhoto::query()->create([
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'caption' => $caption,
            'photo_path' => $path,
            'product_id' => $productId,
        ]);
    }
}
