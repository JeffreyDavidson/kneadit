<?php

namespace App\Filament\Central\Resources\EmailCampaignResource\Pages;

use App\Filament\Central\Resources\EmailCampaignResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmailCampaigns extends ListRecords
{
    protected static string $resource = EmailCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New Campaign'),
        ];
    }
}
