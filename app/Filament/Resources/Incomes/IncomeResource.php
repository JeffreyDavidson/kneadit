<?php

namespace App\Filament\Resources\Incomes;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\Incomes\Pages\ListIncomes;
use App\Filament\Resources\Incomes\Schemas\IncomeForm;
use App\Filament\Resources\Incomes\Tables\IncomesTable;
use App\Models\Financial\Income;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Laravel\Pennant\Feature;

class IncomeResource extends Resource
{
    use ShowsUpgradeBadge;

    #[\Override]
    protected static ?string $model = Income::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'description';

    #[\Override]
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingUp;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    #[\Override]
    protected static ?int $navigationSort = 2;

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return IncomeForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return IncomesTable::configure($table);
    }

    #[\Override]
    public static function canAccess(): bool
    {
        return Feature::active('growth-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Growth;
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListIncomes::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) cache()->remember('navigation-badge:incomes:count', 60, fn (): int => static::getModel()::query()->count());
    }
}
