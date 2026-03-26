<?php

namespace App\Filament\Central\Resources;

use App\Enums\EmailCampaignStatus;
use App\Filament\Central\Resources\EmailCampaignResource\Pages;
use App\Models\EmailCampaign;
use App\Models\Tenant;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class EmailCampaignResource extends Resource
{
    protected static ?string $model = EmailCampaign::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|UnitEnum|null $navigationGroup = 'Communication';

    protected static ?string $navigationLabel = 'Email Campaigns';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('subject')
                    ->required()
                    ->maxLength(255),
                Textarea::make('body')
                    ->required()
                    ->columnSpanFull(),
                Select::make('target_segment')
                    ->options([
                        'all' => 'All',
                        'starter' => 'Starter',
                        'growth' => 'Growth',
                        'pro' => 'Pro',
                        'trial' => 'Trial',
                        'inactive' => 'Inactive',
                    ])
                    ->required()
                    ->default('all'),
            ]);
    }

    public static function table(Table $table): Table
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
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'scheduled' => 'info',
                        'sent' => 'success',
                        default => 'gray',
                    }),
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
            ->actions([
                Actions\EditAction::make()->slideOver(),
                Actions\Action::make('send_now')
                    ->label('Send Now')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Send Campaign Now')
                    ->modalDescription('Are you sure you want to send this campaign immediately?')
                    ->action(function (EmailCampaign $record) {
                        $recipientCount = Tenant::query()->count();
                        $record->update([
                            'status' => 'sent',
                            'sent_at' => now(),
                            'recipient_count' => $recipientCount,
                        ]);
                    })
                    ->visible(fn (EmailCampaign $record) => $record->status !== EmailCampaignStatus::Sent),
                Actions\Action::make('schedule')
                    ->label('Schedule')
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->form([
                        DateTimePicker::make('scheduled_at')
                            ->label('Schedule At')
                            ->required(),
                    ])
                    ->action(function (EmailCampaign $record, array $data) {
                        $record->update([
                            'status' => 'scheduled',
                            'scheduled_at' => $data['scheduled_at'],
                        ]);
                    })
                    ->visible(fn (EmailCampaign $record) => $record->status !== EmailCampaignStatus::Sent),
                Actions\Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (EmailCampaign $record) => 'Preview: ' . $record->subject)
                    ->modalContent(fn (EmailCampaign $record) => view('filament.central.partials.email-preview', ['campaign' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailCampaigns::route('/'),
        ];
    }
}
