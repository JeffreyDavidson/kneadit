<?php

namespace App\Filament\Pages;

use App\Enums\PlatformSenderType;
use App\Models\PlatformMessage;
use App\Models\Tenant;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;

class Messages extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Messages';

    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.messages';

    public ?int $viewingMessage = null;

    public string $replyBody = '';

    public function getTitle(): string
    {
        return 'Messages';
    }

    /** @return Collection<int, PlatformMessage> */
    public function getMessages(): Collection
    {
        /** @var Tenant|null $tenant */
        $tenant = Filament::getTenant();

        return PlatformMessage::query()->where('tenant_id', $tenant?->id)
            ->topLevel()
            ->orderByRaw('is_read ASC')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function viewThread(int $messageId): void
    {
        $this->viewingMessage = $messageId;

        // Mark as read
        $message = PlatformMessage::query()->find($messageId);
        if ($message && ! $message->is_read && $message->sender_type === PlatformSenderType::Admin) {
            $message->update(['is_read' => true, 'read_at' => now()]);
        }
    }

    /** @return Collection<int, PlatformMessage>|null */
    public function getThread(): ?Collection
    {
        if (! $this->viewingMessage) {
            return null;
        }

        return PlatformMessage::query()->where('parent_id', $this->viewingMessage)->oldest()
            ->get();
    }

    public function getViewingRecord(): ?PlatformMessage
    {
        return $this->viewingMessage ? PlatformMessage::query()->find($this->viewingMessage) : null;
    }

    public function sendReply(): void
    {
        $this->validate(['replyBody' => ['required', 'string', 'min:1']]);

        $parent = PlatformMessage::query()->findOrFail($this->viewingMessage);
        /** @var Tenant|null $tenant */
        $tenant = Filament::getTenant();

        PlatformMessage::query()->create([
            'tenant_id' => $tenant?->id,
            'sender_type' => PlatformSenderType::Tenant,
            'subject' => 'Re: '.$parent->subject,
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
        /** @var Tenant|null $tenant */
        $tenant = Filament::getTenant();
        if (! $tenant) {
            return null;
        }

        $count = PlatformMessage::query()->where('tenant_id', $tenant->id)
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
