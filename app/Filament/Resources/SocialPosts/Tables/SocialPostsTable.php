<?php

namespace App\Filament\Resources\SocialPosts\Tables;

use App\Enums\SocialPostStatus;
use App\Models\SocialPost;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SocialPostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['product']))
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
                Action::make('schedule')
                    ->icon('heroicon-o-calendar')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Schedule Post')
                    ->modalDescription('Mark this post as scheduled?')
                    ->action(function (SocialPost $record) {
                        $record->update(['status' => SocialPostStatus::Scheduled]);
                        Notification::make()
                            ->title('Post scheduled')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (SocialPost $record) => $record->status === SocialPostStatus::Draft),

                Action::make('mark_posted')
                    ->label('Mark Posted')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Mark as Posted')
                    ->modalDescription('Mark this post as posted?')
                    ->action(function (SocialPost $record) {
                        $record->update(['status' => SocialPostStatus::Posted]);
                        Notification::make()
                            ->title('Post marked as posted')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (SocialPost $record) => $record->status === SocialPostStatus::Scheduled),

                Action::make('revert_to_draft')
                    ->label('Revert to Draft')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Revert to Draft')
                    ->modalDescription('Move this post back to draft status?')
                    ->action(function (SocialPost $record) {
                        $record->update(['status' => SocialPostStatus::Draft]);
                        Notification::make()
                            ->title('Post reverted to draft')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (SocialPost $record) => $record->status === SocialPostStatus::Scheduled),

                EditAction::make()
                    ->slideOver()
                    ->modalWidth('md'),
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
