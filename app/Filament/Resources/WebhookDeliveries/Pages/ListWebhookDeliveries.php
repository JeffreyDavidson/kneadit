<?php

namespace App\Filament\Resources\WebhookDeliveries\Pages;

use App\Filament\Resources\WebhookDeliveries\WebhookDeliveryResource;
use Filament\Resources\Pages\ListRecords;

class ListWebhookDeliveries extends ListRecords
{
    #[\Override]
    protected static string $resource = WebhookDeliveryResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [];
    }
}
