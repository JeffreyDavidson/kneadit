<?php

namespace App\Filament\Resources\CapacityLimits\Pages;

use App\Filament\Resources\CapacityLimits\CapacityLimitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCapacityLimits extends ListRecords
{
    protected static string $resource = CapacityLimitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->slideOver()->slideOver()->modalWidth('md'),
        ];
    }
}
