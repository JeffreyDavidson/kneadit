<?php

namespace App\Filament\Central\Resources\BlogPostResource\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BlogPostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('category')
                    ->badge()
                    ->colors([
                        'primary' => 'guides',
                        'success' => 'tips',
                        'warning' => 'laws',
                        'info' => 'news',
                    ]),
                IconColumn::make('is_published')
                    ->boolean()
                    ->label('Published'),
                TextColumn::make('published_at')
                    ->dateTime('M j, Y')
                    ->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'guides' => 'Getting Started',
                        'laws' => 'Cottage Food Laws',
                        'tips' => 'Baker Tips',
                        'news' => 'KneadIt News',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
