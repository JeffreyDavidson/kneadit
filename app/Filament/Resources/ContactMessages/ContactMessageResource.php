<?php

namespace App\Filament\Resources\ContactMessages;

use App\Enums\UserRole;
use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Filament\Resources\ContactMessages\Schemas\ContactMessageForm;
use App\Filament\Resources\ContactMessages\Tables\ContactMessagesTable;
use App\Filament\Traits\RequiresRole;
use App\Models\ContactMessage;
use App\Traits\HasPlanGating;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ContactMessageResource extends Resource
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): UserRole
    {
        return UserRole::Manager;
    }

    protected static ?string $model = ContactMessage::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    protected static ?string $navigationLabel = 'Messages';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return ContactMessageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContactMessagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'subject'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->subject ?? 'Message from '.$record->name;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'From' => $record->name,
            'Email' => $record->email ?? 'N/A',
            'Read' => $record->is_read ? 'Yes' : 'No',
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactMessages::route('/'),
        ];
    }
}
