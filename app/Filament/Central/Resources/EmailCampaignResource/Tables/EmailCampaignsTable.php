<?php

namespace App\Filament\Central\Resources\EmailCampaignResource\Tables;

use App\Actions\Platform\ScheduleEmailCampaign;
use App\Actions\Platform\SendEmailCampaign;
use App\Enums\Marketing\EmailCampaignStatus;
use App\Models\Engagement\EmailCampaign;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
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
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('target_segment')
                    ->label('Segment')
                    ->badge(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('recipient_count')
                    ->label('Recipients')
                    ->sortable(),
                TextColumn::make('scheduled_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('sent_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(EmailCampaignStatus::class),
            ])
            ->recordActions([
                Actions\EditAction::make()
                    ->slideOver()
                    ->visible(fn (EmailCampaign $record) => $record->status !== EmailCampaignStatus::Sent
                        && $record->status !== EmailCampaignStatus::Sending),
                Actions\ViewAction::make()
                    ->slideOver()
                    ->visible(fn (EmailCampaign $record) => $record->status === EmailCampaignStatus::Sent
                        || $record->status === EmailCampaignStatus::Sending),
                Actions\Action::make('send_now')
                    ->label('Send Now')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->color('success')
                    ->authorize('update')
                    ->requiresConfirmation()
                    ->modalHeading('Send Campaign Now')
                    ->modalDescription('Are you sure you want to send this campaign immediately?')
                    ->action(fn (EmailCampaign $record) => resolve(SendEmailCampaign::class)($record))
                    ->visible(fn (EmailCampaign $record) => $record->status !== EmailCampaignStatus::Sent
                        && $record->status !== EmailCampaignStatus::Sending),
                Actions\Action::make('schedule')
                    ->label('Schedule')
                    ->icon(Heroicon::OutlinedClock)
                    ->color('info')
                    ->authorize('update')
                    ->schema([
                        DateTimePicker::make('scheduled_at')
                            ->label('Schedule At')
                            ->required(),
                    ])
                    ->action(fn (EmailCampaign $record, array $data) => resolve(ScheduleEmailCampaign::class)($record, $data['scheduled_at']))
                    ->visible(fn (EmailCampaign $record) => $record->status === EmailCampaignStatus::Draft),
            ])
            ->toolbarActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }
}
