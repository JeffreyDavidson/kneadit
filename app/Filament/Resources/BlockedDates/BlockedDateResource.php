<?php

namespace App\Filament\Resources\BlockedDates;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\BlockedDates\Pages\ListBlockedDates;
use App\Filament\Resources\BlockedDates\Schemas\BlockedDateForm;
use App\Filament\Resources\BlockedDates\Tables\BlockedDatesTable;
use App\Models\Operations\BlockedDate;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Laravel\Pennant\Feature;

class BlockedDateResource extends Resource
{
    use ShowsUpgradeBadge;

    #[\Override]
    protected static ?string $model = BlockedDate::class;

    #[\Override]
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    #[\Override]
    protected static ?int $navigationSort = 8;

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return BlockedDateForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return BlockedDatesTable::configure($table);
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

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListBlockedDates::route('/'),
        ];
    }
}
