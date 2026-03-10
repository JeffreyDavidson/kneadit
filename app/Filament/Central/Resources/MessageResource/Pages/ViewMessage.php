<?php

namespace App\Filament\Central\Resources\MessageResource\Pages;

use App\Filament\Central\Resources\MessageResource;
use App\Models\PlatformMessage;
use Filament\Resources\Pages\ViewRecord;

class ViewMessage extends ViewRecord
{
    protected static string $resource = MessageResource::class;

    protected string $view = 'filament.central.pages.view-message';

    public string $replyBody = '';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Mark as read
        if (! $this->record->is_read) {
            $this->record->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    public function getTitle(): string
    {
        return $this->record->subject;
    }

    public function getThread(): \Illuminate\Database\Eloquent\Collection
    {
        return PlatformMessage::where('parent_id', $this->record->id)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function sendReply(): void
    {
        $this->validate([
            'replyBody' => 'required|string|min:1',
        ]);

        PlatformMessage::create([
            'tenant_id' => $this->record->tenant_id,
            'sender_type' => 'admin',
            'subject' => 'Re: ' . $this->record->subject,
            'body' => $this->replyBody,
            'parent_id' => $this->record->id,
        ]);

        $this->replyBody = '';
    }
}
