<?php

declare(strict_types=1);

namespace App\Filament\Resources\LoyaltyRewards\Pages;

use App\Filament\Resources\LoyaltyRewards\LoyaltyRewardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLoyaltyRewards extends ListRecords
{
    #[\Override]
    protected static string $resource = LoyaltyRewardResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver(),
        ];
    }
}
