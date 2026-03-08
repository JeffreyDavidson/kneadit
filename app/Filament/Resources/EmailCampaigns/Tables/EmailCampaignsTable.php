<?php

namespace App\Filament\Resources\EmailCampaigns\Tables;

use App\Mail\CustomerBlast;
use App\Models\Customer;
use App\Models\EmailCampaign;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

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
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sending' => 'warning',
                        'sent' => 'success',
                        default => 'gray',
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
            ->actions([
                Action::make('send')
                    ->label('Send Campaign')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Send Campaign')
                    ->modalDescription('This will send this email to all customers. Are you sure?')
                    ->visible(fn (EmailCampaign $record) => $record->status === 'draft')
                    ->action(function (EmailCampaign $record) {
                        $record->update(['status' => 'sending']);

                        $emails = Customer::whereNotNull('email')
                            ->distinct()
                            ->pluck('email');

                        foreach ($emails as $email) {
                            Mail::to($email)->queue(
                                new CustomerBlast($record->subject, $record->body)
                            );
                        }

                        $record->update([
                            'status' => 'sent',
                            'sent_at' => now(),
                            'recipient_count' => $emails->count(),
                        ]);
                    }),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
