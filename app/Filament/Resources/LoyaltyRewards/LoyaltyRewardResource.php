<?php

namespace App\Filament\Resources\LoyaltyRewards;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\LoyaltyRewards\Pages\ListLoyaltyRewards;
use App\Filament\Resources\LoyaltyRewards\Schemas\LoyaltyRewardForm;
use App\Filament\Resources\LoyaltyRewards\Tables\LoyaltyRewardsTable;
use App\Models\Engagement\LoyaltyReward;
use App\Presenters\LoyaltyRewardPresenter;
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

    #[\Override]
    protected static ?string $model = LoyaltyReward::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Shop';

    #[\Override]
    protected static ?int $navigationSort = 14;

    #[\Override]
    protected static ?string $navigationLabel = 'Loyalty Rewards';

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return LoyaltyRewardForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return LoyaltyRewardsTable::configure($table);
    }

    #[\Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    #[\Override]
    public static function canAccess(): bool
    {
        return Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    /** @param LoyaltyReward $record */
    #[\Override]
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }

    /** @param LoyaltyReward $record */
    #[\Override]
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Points' => (string) $record->points_required,
            'Type' => LoyaltyRewardPresenter::for($record)->rewardTypeLabel(),
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListLoyaltyRewards::route('/'),
        ];
    }
}
