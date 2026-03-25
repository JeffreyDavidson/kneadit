<?php

namespace App\Filament\Resources\CustomerPhotos;

use App\Enums\UserRole;
use App\Filament\Resources\CustomerPhotos\Pages\ListCustomerPhotos;
use App\Filament\Resources\CustomerPhotos\Schemas\CustomerPhotoForm;
use App\Filament\Resources\CustomerPhotos\Tables\CustomerPhotosTable;
use App\Filament\Traits\RequiresRole;
use App\Models\CustomerPhoto;
use App\Traits\HasPlanGating;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CustomerPhotoResource extends Resource
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): UserRole
    {
        return UserRole::Manager;
    }

    protected static string $requiredPlan = 'pro';

    protected static ?string $model = CustomerPhoto::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    protected static ?string $navigationLabel = 'Customer Photos';

    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        return CustomerPhotoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerPhotosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerPhotos::route('/'),
        ];
    }
}
