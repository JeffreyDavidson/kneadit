<?php

declare(strict_types=1);

namespace App\Filament\Resources\GalleryPhotos\Pages;

use App\Filament\Resources\GalleryPhotos\GalleryPhotoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGalleryPhotos extends ListRecords
{
    #[\Override]
    protected static string $resource = GalleryPhotoResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->slideOver(),
        ];
    }
}
