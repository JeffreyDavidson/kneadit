<?php

declare(strict_types=1);

namespace App\Filament\Central\Resources\ScheduledCheckinResource\Pages;

use App\Filament\Central\Resources\ScheduledCheckinResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScheduledCheckins extends ListRecords
{
    #[\Override]
    protected static string $resource = ScheduledCheckinResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->slideOver(),
        ];
    }
}
