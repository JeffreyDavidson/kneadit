<?php

namespace App\Filament\Resources\SocialPosts\Tables;

use App\Actions\Content\TransitionSocialPostStatus;
use App\Enums\Marketing\SocialPlatform;
use App\Enums\Marketing\SocialPostStatus;
use App\Models\Content\SocialPost;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
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
                TextColumn::make('platform')
                    ->badge()
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

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('platform')
                    ->options(SocialPlatform::class),
                SelectFilter::make('status')
                    ->options(SocialPostStatus::class),
            ])
            ->recordActions([
                Action::make('schedule')
                    ->icon(Heroicon::OutlinedCalendar)
                    ->color('warning')
                    ->authorize('update')
                    ->requiresConfirmation()
                    ->modalHeading('Schedule Post')
                    ->modalDescription('Mark this post as scheduled?')
                    ->action(function (SocialPost $record) {
                        resolve(TransitionSocialPostStatus::class)($record, SocialPostStatus::Scheduled);
                        Notification::make()
                            ->title('Post scheduled')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (SocialPost $record) => $record->status === SocialPostStatus::Draft),

                Action::make('mark_posted')
                    ->label('Mark Posted')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->authorize('update')
                    ->requiresConfirmation()
                    ->modalHeading('Mark as Posted')
                    ->modalDescription('Mark this post as posted?')
                    ->action(function (SocialPost $record) {
                        resolve(TransitionSocialPostStatus::class)($record, SocialPostStatus::Posted);
                        Notification::make()
                            ->title('Post marked as posted')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (SocialPost $record) => $record->status === SocialPostStatus::Scheduled),

                Action::make('revert_to_draft')
                    ->label('Revert to Draft')
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->color('gray')
                    ->authorize('update')
                    ->requiresConfirmation()
                    ->modalHeading('Revert to Draft')
                    ->modalDescription('Move this post back to draft status?')
                    ->action(function (SocialPost $record) {
                        resolve(TransitionSocialPostStatus::class)($record, SocialPostStatus::Draft);
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
