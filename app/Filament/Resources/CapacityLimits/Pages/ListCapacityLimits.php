<?php

declare(strict_types=1);

namespace App\Filament\Resources\CapacityLimits\Pages;

use App\Filament\Resources\CapacityLimits\CapacityLimitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCapacityLimits extends ListRecords
{
    #[\Override]
    protected static string $resource = CapacityLimitResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->slideOver()->slideOver()->modalWidth('md')
                ->mutateDataUsing(function (array $data): array {
                    $data['date'] = $data['specific_date'] ?? now()->toDateString();

                    return $data;
                }),
        ];
    }
}
