<?php

namespace App\Filament\Resources\LoyaltyRewards;

use App\Enums\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\LoyaltyRewards\Pages\ListLoyaltyRewards;
use App\Filament\Resources\LoyaltyRewards\Schemas\LoyaltyRewardForm;
use App\Filament\Resources\LoyaltyRewards\Tables\LoyaltyRewardsTable;
use App\Models\LoyaltyReward;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Laravel\Pennant\Feature;

class LoyaltyRewardResource extends Resource
{
    use ShowsUpgradeBadge;

    protected static ?string $model = LoyaltyReward::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static string|\UnitEnum|null $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 14;

    protected static ?string $navigationLabel = 'Loyalty Rewards';

    public static function form(Schema $schema): Schema
    {
        return LoyaltyRewardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoyaltyRewardsTable::configure($table);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function canAccess(): bool
    {
        return Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    /** @param LoyaltyReward $record */
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }

    /** @param LoyaltyReward $record */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Points' => (string) $record->points_required,
            'Type' => $record->reward_type_label,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoyaltyRewards::route('/'),
        ];
    }
}
