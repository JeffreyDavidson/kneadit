<?php

namespace App\Filament\Central\Resources\ScheduledCheckinResource\Pages;

use App\Filament\Central\Resources\ScheduledCheckinResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScheduledCheckins extends ListRecords
{
    protected static string $resource = ScheduledCheckinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->slideOver(),
        ];
    }
}
