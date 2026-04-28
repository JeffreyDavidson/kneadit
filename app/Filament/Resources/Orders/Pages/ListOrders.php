<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Match the modal width that SlideOverEditAction sets ('md') so
            // Add Order and Edit Order render at the same size — the default
            // Filament slide-over is wider, which created an asymmetric pair.
            CreateAction::make()
                ->slideOver()
                ->modalWidth('md'),
        ];
    }
}
