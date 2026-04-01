<?php

namespace App\Filament\Resources\BlockedDateResource\Pages;

use App\Filament\Resources\BlockedDateResource\BlockedDateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBlockedDates extends ListRecords
{
    protected static string $resource = BlockedDateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver(),
        ];
    }
}
