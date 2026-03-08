<?php

namespace App\Filament\Resources\SocialPosts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SocialPostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                BadgeColumn::make('platform')
                    ->colors([
                        'pink' => 'instagram',
                        'info' => 'facebook',
                        'gray' => 'tiktok',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'instagram' => '📸 Instagram',
                        'facebook' => '📘 Facebook',
                        'tiktok' => '🎵 TikTok',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('caption')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('product.name')
                    ->label('Product')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('scheduled_for')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->placeholder('Not scheduled'),

                BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'scheduled',
                        'success' => 'posted',
                    ]),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('platform')
                    ->options([
                        'instagram' => 'Instagram',
                        'facebook' => 'Facebook',
                        'tiktok' => 'TikTok',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'scheduled' => 'Scheduled',
                        'posted' => 'Posted',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scheduled_for', 'desc')
            ->emptyStateHeading('No social posts yet')
            ->emptyStateDescription('Create your first social media post to start scheduling content.');
    }
}
