<?php

namespace App\Filament\Resources\CateringInquiries\Pages;

use App\Filament\Resources\CateringInquiries\CateringInquiryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCateringInquiry extends EditRecord
{
    protected static string $resource = CateringInquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
