<?php

namespace App\Filament\Resources\EmailCampaigns\Tables;

use App\Actions\Platform\SendEmailCampaign;
use App\Enums\Marketing\EmailCampaignStatus;
use App\Filament\Actions\SlideOverEditAction;
use App\Models\Engagement\EmailCampaign;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmailCampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')
                    ->sortable()
                    ->searchable()
                    ->limit(50),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('recipient_count')
                    ->label('Recipients')
                    ->sortable(),

                TextColumn::make('sent_at')
                    ->label('Sent')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(EmailCampaignStatus::class),
            ])
            ->recordActions([
                Action::make('send')
                    ->label('Send Campaign')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->color('success')
                    ->authorize('update')
                    ->requiresConfirmation()
                    ->modalHeading('Send Campaign')
                    ->modalDescription('This will send this email to all customers. Are you sure?')
                    ->visible(fn (EmailCampaign $record) => $record->status === EmailCampaignStatus::Draft)
                    ->action(fn (EmailCampaign $record) => resolve(SendEmailCampaign::class)($record)),
                SlideOverEditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No email campaigns yet')
            ->emptyStateDescription('Create your first campaign to reach your customers.');
    }
}
