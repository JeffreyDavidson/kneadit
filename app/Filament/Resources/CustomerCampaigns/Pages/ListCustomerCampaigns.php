<?php

namespace App\Filament\Resources\CustomerCampaigns\Pages;

use App\Filament\Resources\CustomerCampaigns\CustomerCampaignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomerCampaigns extends ListRecords
{
    protected static string $resource = CustomerCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
