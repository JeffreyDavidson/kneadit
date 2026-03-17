<?php

namespace App\Filament\Resources\CateringInquiries;

use App\Filament\Resources\CateringInquiries\Pages\CreateCateringInquiry;
use App\Filament\Resources\CateringInquiries\Pages\EditCateringInquiry;
use App\Filament\Resources\CateringInquiries\Pages\ListCateringInquiries;
use App\Filament\Resources\CateringInquiries\Schemas\CateringInquiryForm;
use App\Filament\Resources\CateringInquiries\Tables\CateringInquiriesTable;
use App\Models\CateringInquiry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CateringInquiryResource extends Resource
{
    protected static ?string $model = CateringInquiry::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cake';

    protected static string|\UnitEnum|null $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationLabel = 'Catering';

    protected static ?string $modelLabel = 'Catering Inquiry';

    protected static ?string $pluralModelLabel = 'Catering Inquiries';

    public static function form(Schema $schema): Schema
    {
        return CateringInquiryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CateringInquiriesTable::configure($table);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['customer_name', 'customer_email'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->customer_name;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Email' => $record->customer_email ?? 'N/A',
            'Event' => $record->event_type_label,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCateringInquiries::route('/'),
            'create' => CreateCateringInquiry::route('/create'),
            'edit' => EditCateringInquiry::route('/{record}/edit'),
        ];
    }
}
