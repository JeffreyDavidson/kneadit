<?php

namespace App\Filament\Resources\Coupons;

use App\Enums\CouponType;
use App\Enums\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\Coupons\Pages\ListCoupons;
use App\Filament\Resources\Coupons\Schemas\CouponForm;
use App\Filament\Resources\Coupons\Tables\CouponsTable;
use App\Models\Coupon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;
use Laravel\Pennant\Feature;

class CouponResource extends Resource
{
    use ShowsUpgradeBadge;

    protected static ?string $model = Coupon::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static string|\UnitEnum|null $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return CouponForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CouponsTable::configure($table);
    }

    public static function canAccess(): bool
    {
        return Feature::active('growth-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Growth;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['code'];
    }

    /** @param Coupon $record */
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->code;
    }

    /** @param Coupon $record */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Type' => $record->type->getLabel(),
            'Value' => $record->type === CouponType::Percentage ? $record->value . '%' : Number::currency($record->value),
            'Active' => $record->is_active ? 'Yes' : 'No',
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCoupons::route('/'),
        ];
    }
}
