<?php

namespace App\Filament\Resources\CustomerPhotos\Pages;

use App\Filament\Resources\CustomerPhotos\CustomerPhotoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerPhoto extends EditRecord
{
    protected static string $resource = CustomerPhotoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
