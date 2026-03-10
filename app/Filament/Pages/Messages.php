<?php

namespace App\Filament\Pages;

use App\Models\PlatformMessage;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Messages extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?string $navigationLabel = 'Messages';

    protected static ?int $navigationSort = 90;

    protected static string $view = 'filament.pages.messages';

    public ?int $viewingMessage = null;

    public string $replyBody = '';

    public function getTitle(): string
    {
        return 'Messages';
    }

    public function getMessages(): \Illuminate\Database\Eloquent\Collection
    {
        $tenant = Filament::getTenant();

        return PlatformMessage::where('tenant_id', $tenant->id)
            ->topLevel()
            ->orderByRaw('is_read ASC')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function viewThread(int $messageId): void
    {
        $this->viewingMessage = $messageId;

        // Mark as read
        $message = PlatformMessage::find($messageId);
        if ($message && ! $message->is_read && $message->sender_type === 'admin') {
            $message->update(['is_read' => true, 'read_at' => now()]);
        }
    }

    public function getThread(): ?\Illuminate\Database\Eloquent\Collection
    {
        if (! $this->viewingMessage) {
            return null;
        }

        return PlatformMessage::where('parent_id', $this->viewingMessage)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getViewingRecord(): ?PlatformMessage
    {
        return $this->viewingMessage ? PlatformMessage::find($this->viewingMessage) : null;
    }

    public function sendReply(): void
    {
        $this->validate(['replyBody' => 'required|string|min:1']);

        $parent = PlatformMessage::findOrFail($this->viewingMessage);
        $tenant = Filament::getTenant();

        PlatformMessage::create([
            'tenant_id' => $tenant->id,
            'sender_type' => 'tenant',
            'subject' => 'Re: ' . $parent->subject,
            'body' => $this->replyBody,
            'parent_id' => $parent->id,
        ]);

        $this->replyBody = '';
    }

    public function backToList(): void
    {
        $this->viewingMessage = null;
        $this->replyBody = '';
    }

    public static function getNavigationBadge(): ?string
    {
        $tenant = Filament::getTenant();
        if (! $tenant) {
            return null;
        }

        $count = PlatformMessage::where('tenant_id', $tenant->id)
            ->fromAdmin()
            ->topLevel()
            ->unread()
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }
}
