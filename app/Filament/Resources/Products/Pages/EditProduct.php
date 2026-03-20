<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Mail\ProductAvailable;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

class EditProduct extends EditRecord
{

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('notifyWaitlist')
                ->label('Notify Waitlist')
                ->icon('heroicon-o-bell-alert')
                ->color('warning')
                ->visible(fn () => $this->record->waitlistEntries()->whereNull('notified_at')->count() > 0)
                ->requiresConfirmation()
                ->modalHeading('Notify Waitlist')
                ->modalDescription(fn () => "Send availability notification to {$this->record->pendingWaitlistCount()} customer(s) waiting for {$this->record->name}?")
                ->action(function () {
                    $entries = $this->record->waitlistEntries()->whereNull('notified_at')->get();
                    $count = 0;

                    foreach ($entries as $entry) {
                        Mail::to($entry->customer_email)
                            ->send(new ProductAvailable($this->record, $entry->customer_name ?? ''));

                        $entry->update(['notified_at' => now()]);
                        $count++;
                    }

                    Notification::make()
                        ->title("Notified {$count} customer(s)")
                        ->success()
                        ->send();
                }),

            DeleteAction::make(),
        ];
    }
}
