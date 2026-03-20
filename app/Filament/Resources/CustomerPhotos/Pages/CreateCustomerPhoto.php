<?php

namespace App\Filament\Resources\CustomerPhotos\Pages;

use App\Filament\Resources\CustomerPhotos\CustomerPhotoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerPhoto extends CreateRecord
{
    use CreateRecord\Concerns\HasSlideOverForm;

    protected static string $resource = CustomerPhotoResource::class;
}
