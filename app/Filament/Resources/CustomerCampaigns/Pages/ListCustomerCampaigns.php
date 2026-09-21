<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerCampaigns\Pages;

use App\Filament\Resources\CustomerCampaigns\CustomerCampaignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomerCampaigns extends ListRecords
{
    #[\Override]
    protected static string $resource = CustomerCampaignResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
