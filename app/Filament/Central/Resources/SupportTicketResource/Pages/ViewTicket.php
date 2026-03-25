<?php

namespace App\Filament\Central\Resources\SupportTicketResource\Pages;

use App\Filament\Central\Resources\SupportTicketResource;
use Filament\Resources\Pages\ViewRecord;
use Livewire\Attributes\Rule;

class ViewTicket extends ViewRecord
{
    protected static string $resource = SupportTicketResource::class;

    protected string $view = 'filament.central.pages.view-ticket';

    #[Rule('required|min:3')]
    public string $replyBody = '';

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
    }

    public function updateStatus(string $status): void
    {
        $data = ['status' => $status];

        if ($status === 'resolved') {
            $data['resolved_at'] = now();
        }

        $this->record->update($data);
        $this->record->refresh();
    }
}
