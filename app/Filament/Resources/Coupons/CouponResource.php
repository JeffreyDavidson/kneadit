<?php

namespace App\Filament\Resources\Coupons;

use App\Enums\CouponType;
use App\Enums\UserRole;
use App\Filament\Resources\Coupons\Pages\ListCoupons;
use App\Filament\Resources\Coupons\Schemas\CouponForm;
use App\Filament\Resources\Coupons\Tables\CouponsTable;
use App\Filament\Traits\RequiresRole;
use App\Models\Coupon;
use App\Traits\HasPlanGating;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CouponResource extends Resource
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): UserRole
    {
        return UserRole::Manager;
    }

    protected static ?string $model = Coupon::class;

    protected static string $requiredPlan = 'growth';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

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

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->code;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Type' => ucfirst($record->type ?? 'N/A'),
            'Value' => $record->type === CouponType::Percentage ? $record->value.'%' : '$'.number_format($record->value, 2),
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
