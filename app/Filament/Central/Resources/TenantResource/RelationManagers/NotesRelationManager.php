<?php

namespace App\Filament\Central\Resources\TenantResource\RelationManagers;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Textarea::make('body')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('author')
                    ->default('admin'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
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
            ]);
    }
}
