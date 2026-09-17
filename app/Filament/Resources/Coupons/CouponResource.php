<?php

namespace App\Filament\Resources\Coupons;

use App\Enums\Financial\CouponType;
use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\Coupons\Pages\ListCoupons;
use App\Filament\Resources\Coupons\Schemas\CouponForm;
use App\Filament\Resources\Coupons\Tables\CouponsTable;
use App\Models\Financial\Coupon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Laravel\Pennant\Feature;

class CouponResource extends Resource
{
    use ShowsUpgradeBadge;

    #[\Override]
    protected static ?string $model = Coupon::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'code';

    #[\Override]
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Shop';

    #[\Override]
    protected static ?int $navigationSort = 5;

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return CouponForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return CouponsTable::configure($table);
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
    public static function getGloballySearchableAttributes(): array
    {
        return ['code'];
    }

    /** @param Coupon $record */
    #[\Override]
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->code;
    }

    /** @param Coupon $record */
    #[\Override]
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Type' => $record->type->getLabel(),
            'Value' => (string) ($record->type === CouponType::Percentage ? $record->percentage : $record->fixed_amount),
            'Active' => $record->is_active ? 'Yes' : 'No',
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListCoupons::route('/'),
        ];
    }
}
