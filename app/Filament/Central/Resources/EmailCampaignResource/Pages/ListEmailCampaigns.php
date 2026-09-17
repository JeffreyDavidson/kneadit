<?php

declare(strict_types=1);

namespace App\Filament\Central\Resources\EmailCampaignResource\Pages;

use App\Filament\Central\Resources\EmailCampaignResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmailCampaigns extends ListRecords
{
    #[\Override]
    protected static string $resource = EmailCampaignResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New Campaign')->slideOver(),
        ];
    }
}
