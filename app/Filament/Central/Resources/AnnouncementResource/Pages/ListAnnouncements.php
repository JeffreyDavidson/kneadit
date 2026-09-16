<?php

namespace App\Filament\Central\Resources\AnnouncementResource\Pages;

use App\Filament\Central\Resources\AnnouncementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAnnouncements extends ListRecords
{
    #[\Override]
    protected static string $resource = AnnouncementResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->slideOver(),
        ];
    }
}
