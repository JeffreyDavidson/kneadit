<?php

namespace App\Filament\Resources\CustomerPhotos\Pages;

use App\Filament\Resources\CustomerPhotos\CustomerPhotoResource;
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
