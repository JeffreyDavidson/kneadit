<?php

namespace App\Filament\Resources\BlogPosts\Tables;

use App\Models\BlogPost;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BlogPostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['tags']))
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

                TextColumn::make('author_name')
                    ->label('Author'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('publish')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Publish Post')
                    ->modalDescription('Are you sure you want to publish this post?')
                    ->action(function (BlogPost $record) {
                        $record->update([
                            'is_published' => true,
                            'published_at' => now(),
                        ]);
                        Notification::make()
                            ->title('Post published')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (BlogPost $record) => ! $record->is_published),

                Action::make('unpublish')
                    ->icon('heroicon-o-arrow-down-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Unpublish Post')
                    ->modalDescription('Are you sure you want to unpublish this post?')
                    ->action(function (BlogPost $record) {
                        $record->update([
                            'is_published' => false,
                            'published_at' => null,
                        ]);
                        Notification::make()
                            ->title('Post unpublished')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (BlogPost $record) => $record->is_published),
            ])
            ->filters([
                SelectFilter::make('is_published')
                    ->label('Status')
                    ->options([
                        '1' => 'Published',
                        '0' => 'Draft',
                    ]),

                SelectFilter::make('category')
                    ->label('Category')
                    ->options(fn () => BlogPost::query()
                        ->whereNotNull('category')
                        ->distinct()
                        ->pluck('category', 'category')
                        ->toArray()
                    ),
            ]);
    }
}
