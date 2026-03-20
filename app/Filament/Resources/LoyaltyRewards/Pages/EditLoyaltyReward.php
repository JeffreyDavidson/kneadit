<?php

namespace App\Filament\Resources\LoyaltyRewards\Pages;

use App\Filament\Resources\LoyaltyRewards\LoyaltyRewardResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLoyaltyReward extends EditRecord
{
    use EditRecord\Concerns\HasSlideOverForm;

    protected static string $resource = LoyaltyRewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
