<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Mail\Customers\ContactMessageReplyMail;
use App\Models\Customers\ContactMessage;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * @property-read ContactMessage $record
 */
class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;

    protected string $view = 'filament.resources.contact-messages.view-contact-message';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reply')
                ->label('Reply')
                ->icon(Heroicon::OutlinedArrowUturnLeft)
                ->color('primary')
                ->modalHeading(fn (): string => 'Reply to ' . $this->record->name)
                ->modalSubmitActionLabel('Send reply')
                ->slideOver()
                ->fillForm(fn (): array => [
                    'subject' => 'Re: ' . $this->record->subject,
                    'body' => '',
                ])
                ->schema([
                    TextInput::make('subject')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('body')
                        ->label('Your reply')
                        ->required()
                        ->rows(10)
                        ->placeholder("Write your response here. The customer's original message will be quoted at the bottom of the email."),
                ])
                ->action(function (array $data): void {
                    DB::transaction(function () use ($data): void {
                        $this->record->replies()->create([
                            'user_id' => auth()->id(),
                            'subject' => $data['subject'],
                            'body' => $data['body'],
                            'sent_at' => now(),
                        ]);

                        if (! $this->record->is_read) {
                            $this->record->update(['is_read' => true]);
                        }
                    });

                    Mail::to($this->record->email)->send(
                        new ContactMessageReplyMail(
                            contactMessage: $this->record,
                            replyBody: $data['body'],
                            replySubject: $data['subject'],
                        ),
                    );

                    Notification::make()
                        ->title('Reply sent')
                        ->body('Email sent to ' . $this->record->email)
                        ->success()
                        ->send();
                }),

            Action::make('toggleRead')
                ->label(fn (): string => $this->record->is_read ? 'Mark Unread' : 'Mark Read')
                ->icon(fn (): Heroicon => $this->record->is_read ? Heroicon::OutlinedEnvelope : Heroicon::OutlinedEnvelopeOpen)
                ->color(fn (): string => $this->record->is_read ? 'gray' : 'success')
                ->action(function (): void {
                    $this->record->update(['is_read' => ! $this->record->is_read]);

                    Notification::make()
                        ->title($this->record->is_read ? 'Marked as read' : 'Marked as unread')
                        ->success()
                        ->send();
                }),
        ];
    }
}
