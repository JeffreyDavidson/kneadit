<?php

namespace App\Actions\Customers;

use App\Events\Customers\CustomerPhotoSubmitted;
use App\Models\Customers\CustomerPhoto;
use App\Services\Support\PrivateImageStore;
use Illuminate\Http\UploadedFile;

class CreateCustomerPhoto
{
    public function __construct(
        private readonly PrivateImageStore $images,
    ) {}

    public function __invoke(
        UploadedFile $photo,
        string $customerName,
        string $customerEmail,
        ?string $caption = null,
        ?int $productId = null,
    ): CustomerPhoto {
        $record = CustomerPhoto::query()->create([
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'caption' => $caption,
            'photo_path' => $this->images->store($photo, 'customer-photos'),
            'product_id' => $productId,
        ]);

        event(new CustomerPhotoSubmitted($record));

        return $record;
    }
}
