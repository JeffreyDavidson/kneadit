<?php

namespace App\Filament\Resources\CustomerCampaigns\Tables;

use App\Actions\Marketing\SendCustomerCampaign;
use App\Enums\Marketing\CustomerCampaignStatus;
use App\Filament\Actions\SlideOverEditAction;
use App\Models\Engagement\CustomerCampaign;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CustomerCampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('target_segment')
                    ->label('Audience')
                    ->badge(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('recipient_count')
                    ->label('Recipients')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('opens')
                    ->label('Opens')
                    ->getStateUsing(function (CustomerCampaign $record): string {
                        $opened = $record->logs()->whereNotNull('opened_at')->count();
                        if ($record->recipient_count === 0) {
                            return '—';
                        }
                        $rate = round($opened / $record->recipient_count * 100);

                        return "{$opened} ({$rate}%)";
                    }),
                TextColumn::make('scheduled_at')
                    ->label('Scheduled')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('sent_at')
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
                    ->options(CustomerCampaignStatus::class),
            ])
            ->recordActions([
                Action::make('send_now')
                    ->label('Send Now')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->color('success')
                    ->authorize('update')
                    ->requiresConfirmation()
                    ->modalHeading('Send this campaign?')
                    ->modalDescription('This will queue the email to every customer in the selected audience. There\'s no undo.')
                    ->visible(fn (CustomerCampaign $record): bool => in_array($record->status, [CustomerCampaignStatus::Draft, CustomerCampaignStatus::Scheduled], true))
                    ->action(function (CustomerCampaign $record): void {
                        $sent = resolve(SendCustomerCampaign::class)($record);
                        Notification::make()
                            ->title("Campaign sent to {$sent} recipient(s)")
                            ->success()
                            ->send();
                    }),
                SlideOverEditAction::make()
                    ->visible(fn (CustomerCampaign $record): bool => in_array($record->status, [CustomerCampaignStatus::Draft, CustomerCampaignStatus::Scheduled], true)),
            ])
            ->emptyStateHeading('No campaigns yet')
            ->emptyStateDescription('Create a campaign to email a segment of your customers.');
    }
}
