<?php

namespace App\Filament\Resources\CustomerPhotos\Pages;

use App\Filament\Resources\CustomerPhotos\CustomerPhotoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomerPhotos extends ListRecords
{
    #[\Override]
    protected static string $resource = CustomerPhotoResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver(),
        ];
    }
}
