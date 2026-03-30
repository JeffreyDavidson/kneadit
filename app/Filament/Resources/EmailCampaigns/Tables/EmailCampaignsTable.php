<?php

namespace App\Filament\Resources\EmailCampaigns\Tables;

use App\Actions\Platform\SendEmailCampaign;
use App\Enums\EmailCampaignStatus;
use App\Models\EmailCampaign;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
                    ->badge()
                    ->color(fn (EmailCampaignStatus $state): string => match ($state) {
                        EmailCampaignStatus::Draft => 'gray',
                        EmailCampaignStatus::Scheduled => 'info',
                        EmailCampaignStatus::Sending => 'warning',
                        EmailCampaignStatus::Sent => 'success',
                    }),

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
            ->actions([
                Action::make('send')
                    ->label('Send Campaign')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Send Campaign')
                    ->modalDescription('This will send this email to all customers. Are you sure?')
                    ->visible(fn (EmailCampaign $record) => $record->status === EmailCampaignStatus::Draft)
                    ->action(fn (EmailCampaign $record) => resolve(SendEmailCampaign::class)($record)),
                EditAction::make()
                    ->slideOver()
                    ->modalWidth('md'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
