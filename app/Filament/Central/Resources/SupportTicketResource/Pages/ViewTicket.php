<?php

namespace App\Filament\Central\Resources\SupportTicketResource\Pages;

use App\Enums\Platform\SupportTicketStatus;
use App\Filament\Central\Resources\SupportTicketResource;
use App\Models\Platform\SupportTicket;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Livewire\Attributes\Rule;

/**
 * @property-read SupportTicket $record
 */
class ViewTicket extends ViewRecord
{
    protected static string $resource = SupportTicketResource::class;

    protected string $view = 'filament.central.pages.view-ticket';

    #[Rule(['required', 'min:3'])]
    public string $replyBody = '';

    public string $adminNotesDraft = '';

    public function mount(int|string $record): void
    {
        parent::mount($record);
        $this->adminNotesDraft = $this->record->admin_notes ?? '';
    }

    public function addReply(): void
    {
        $this->validate(['replyBody' => ['required', 'min:3']]);

        $this->record->replies()->create([
            'author_type' => 'admin',
            'author_name' => auth()->user()->name ?? 'Admin',
            'body' => $this->replyBody,
        ]);

        $this->replyBody = '';
        $this->record->load('replies');

        Notification::make()
            ->title('Reply sent')
            ->success()
            ->send();
    }

    public function updateStatus(string $status): void
    {
        $data = ['status' => $status];

        if ($status === SupportTicketStatus::Resolved->value) {
            $data['resolved_at'] = now();
        }

        $this->record->update($data);
        $this->record->refresh();

        Notification::make()
            ->title('Status updated')
            ->success()
            ->send();
    }

    public function saveAdminNotes(): void
    {
        $this->record->update(['admin_notes' => $this->adminNotesDraft]);
        $this->record->refresh();

        Notification::make()
            ->title('Internal notes saved')
            ->success()
            ->send();
    }
}
