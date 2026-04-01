<?php

namespace App\Filament\Resources\WaitlistEntryResource\Pages;

use App\Filament\Resources\WaitlistEntryResource\WaitlistEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWaitlistEntries extends ListRecords
{
    protected static string $resource = WaitlistEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver(),
        ];
    }
}
