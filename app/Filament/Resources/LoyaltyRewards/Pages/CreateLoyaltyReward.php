<?php

namespace App\Filament\Resources\LoyaltyRewards\Pages;

use App\Filament\Resources\LoyaltyRewards\LoyaltyRewardResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLoyaltyReward extends CreateRecord
{
    use CreateRecord\Concerns\HasSlideOverForm;

    protected static string $resource = LoyaltyRewardResource::class;
}
