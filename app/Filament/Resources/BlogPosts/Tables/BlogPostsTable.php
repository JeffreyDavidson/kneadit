<?php

namespace App\Filament\Resources\BlogPosts\Tables;

use App\Actions\Content\PublishBlogPost;
use App\Actions\Content\UnpublishBlogPost;
use App\Models\Content\TenantBlogPost;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
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
                    ->sortable(),

                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),

                TextColumn::make('published_at')
                    ->label('Published Date')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('tags')
                    ->badge()
                    ->separator(','),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('publish')
                    ->icon(Heroicon::OutlinedArrowUpCircle)
                    ->color('success')
                    ->authorize('update')
                    ->requiresConfirmation()
                    ->modalHeading('Publish Post')
                    ->modalDescription('Are you sure you want to publish this post?')
                    ->action(function (TenantBlogPost $record) {
                        resolve(PublishBlogPost::class)($record);
                        Notification::make()
                            ->title('Post published')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (TenantBlogPost $record) => ! $record->is_published),

                Action::make('unpublish')
                    ->icon(Heroicon::OutlinedArrowDownCircle)
                    ->color('warning')
                    ->authorize('update')
                    ->requiresConfirmation()
                    ->modalHeading('Unpublish Post')
                    ->modalDescription('Are you sure you want to unpublish this post?')
                    ->action(function (TenantBlogPost $record) {
                        resolve(UnpublishBlogPost::class)($record);
                        Notification::make()
                            ->title('Post unpublished')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (TenantBlogPost $record) => $record->is_published),
            ])
            ->filters([
                SelectFilter::make('is_published')
                    ->label('Status')
                    ->options([
                        '1' => 'Published',
                        '0' => 'Draft',
                    ]),

            ])
            ->emptyStateHeading('No blog posts yet')
            ->emptyStateDescription('Write your first blog post to share with customers.');
    }
}
