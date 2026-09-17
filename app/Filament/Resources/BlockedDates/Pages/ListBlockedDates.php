<?php

declare(strict_types=1);

namespace App\Filament\Resources\BlockedDates\Pages;

use App\Filament\Resources\BlockedDates\BlockedDateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBlockedDates extends ListRecords
{
    #[\Override]
    protected static string $resource = BlockedDateResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver(),
        ];
    }
}
