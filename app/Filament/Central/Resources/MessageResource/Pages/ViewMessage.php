<?php

namespace App\Filament\Central\Resources\MessageResource\Pages;

use App\Enums\Platform\PlatformSenderType;
use App\Filament\Central\Resources\MessageResource;
use App\Models\Platform\PlatformMessage;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read PlatformMessage $record
 */
class ViewMessage extends ViewRecord
{
    protected static string $resource = MessageResource::class;

    protected string $view = 'filament.central.pages.view-message';

    public string $replyBody = '';

    public function resolveRecord(int|string $key): Model
    {
        return PlatformMessage::query()->findOrFail($key);
    }

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

    /** @return Collection<int, PlatformMessage> */
    public function getThread(): Collection
    {
        return PlatformMessage::query()->where('parent_id', $this->record->id)->oldest()
            ->get();
    }

    public function sendReply(): void
    {
        $this->validate([
            'replyBody' => ['required', 'string', 'min:1'],
        ]);

        PlatformMessage::query()->create([
            'tenant_id' => $this->record->tenant_id,
            'sender_type' => PlatformSenderType::Admin,
            'subject' => 'Re: ' . $this->record->subject,
            'body' => $this->replyBody,
            'parent_id' => $this->record->id,
        ]);

        $this->replyBody = '';
    }
}
