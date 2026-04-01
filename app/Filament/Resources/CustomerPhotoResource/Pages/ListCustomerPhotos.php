<?php

namespace App\Filament\Resources\CustomerPhotoResource\Pages;

use App\Filament\Resources\CustomerPhotoResource\CustomerPhotoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomerPhotos extends ListRecords
{
    protected static string $resource = CustomerPhotoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver(),
        ];
    }
}
