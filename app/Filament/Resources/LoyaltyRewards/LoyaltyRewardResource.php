<?php

namespace App\Filament\Resources\LoyaltyRewards;

use App\Enums\UserRole;
use App\Filament\Resources\LoyaltyRewards\Pages\ListLoyaltyRewards;
use App\Filament\Resources\LoyaltyRewards\Schemas\LoyaltyRewardForm;
use App\Filament\Resources\LoyaltyRewards\Tables\LoyaltyRewardsTable;
use App\Filament\Traits\RequiresRole;
use App\Models\LoyaltyReward;
use App\Traits\HasPlanGating;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class LoyaltyRewardResource extends Resource
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): UserRole
    {
        return UserRole::Manager;
    }

    protected static string $requiredPlan = 'pro';

    protected static ?string $model = LoyaltyReward::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-gift';

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
