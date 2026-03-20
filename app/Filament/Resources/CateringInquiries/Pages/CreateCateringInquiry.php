<?php

namespace App\Filament\Resources\CateringInquiries\Pages;

use App\Filament\Resources\CateringInquiries\CateringInquiryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCateringInquiry extends CreateRecord
{
    use CreateRecord\Concerns\HasSlideOverForm;

    protected static string $resource = CateringInquiryResource::class;
}
