<?php

namespace App\Filament\Central\Resources\TenantResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    /** No TenantNotePolicy exists; platform admins can freely manage notes. */
    public function canCreate(): bool
    {
        return auth()->check() && Gate::allows('platform-admin');
    }

    public function canEdit(Model $record): bool
    {
        return $this->canCreate();
    }

    public function canDelete(Model $record): bool
    {
        return $this->canCreate();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Textarea::make('body')
                    ->required()
                    ->rows(4)
                    ->placeholder('What happened? What\'s worth remembering about this tenant?')
                    ->columnSpanFull(),
                TextInput::make('author')
                    ->default(fn () => auth()->user()->name ?? 'admin'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->deferLoading(false)
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('body')
                    ->searchable()
                    ->limit(100)
                    ->wrap(),
                TextColumn::make('author'),
                TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add note')
                    ->icon(Heroicon::OutlinedPlus)
                    ->modalHeading('Add tenant note')
                    ->modalSubmitActionLabel('Save note'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
