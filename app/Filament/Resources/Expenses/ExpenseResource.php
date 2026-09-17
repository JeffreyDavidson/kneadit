<?php

namespace App\Filament\Resources\Expenses;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\Expenses\Pages\ListExpenses;
use App\Filament\Resources\Expenses\Schemas\ExpenseForm;
use App\Filament\Resources\Expenses\Tables\ExpensesTable;
use App\Models\Financial\Expense;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Laravel\Pennant\Feature;

class ExpenseResource extends Resource
{
    use ShowsUpgradeBadge;

    #[\Override]
    protected static ?string $model = Expense::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'description';

    #[\Override]
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingDown;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    #[\Override]
    protected static ?int $navigationSort = 1;

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return ExpenseForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return ExpensesTable::configure($table);
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
            'index' => ListExpenses::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) cache()->remember('navigation-badge:expenses:count', 60, fn (): int => static::getModel()::query()->count());
    }
}
