<?php

namespace App\Filament\Resources\CapacityLimits;

use App\Enums\UserRole;
use App\Filament\Resources\CapacityLimits\Schemas\CapacityLimitForm;
use App\Filament\Resources\CapacityLimits\Tables\CapacityLimitsTable;
use App\Filament\Traits\RequiresRole;
use App\Models\CapacityLimit;
use App\Traits\HasPlanGating;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CapacityLimitResource extends Resource
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): UserRole
    {
        return UserRole::Manager;
    }

    protected static string $requiredPlan = 'pro';

    protected static ?string $model = CapacityLimit::class;

    protected static ?string $navigationLabel = 'Capacity Limits';

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-clock';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public static function form(Schema $schema): Schema
    {
        return CapacityLimitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CapacityLimitsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCapacityLimits::route('/'),
        ];
    }
}
