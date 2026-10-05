<?php

namespace App\Filament\Pages\Platform;

use App\Enums\Platform\PlatformSenderType;
use App\Filament\Concerns\RequiresManagerRole;
use App\Models\Platform\PlatformMessage;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Locked;

class Messages extends Page
{
    use RequiresManagerRole;

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    #[\Override]
    protected static bool $shouldRegisterNavigation = false;

    #[\Override]
    protected static ?string $navigationLabel = 'Messages';

    #[\Override]
    protected static ?int $navigationSort = 90;

    #[\Override]
    protected string $view = 'filament.pages.platform.messages';

    #[Locked]
    public ?int $viewingMessage = null;

    public string $replyBody = '';

    #[\Override]
    public function getTitle(): string
    {
        return 'Messages';
    }

    /** @return Collection<int, PlatformMessage> */
    public function inboxMessages(): Collection
    {
        $tenantId = tenant('id');

        if (! is_string($tenantId)) {
            return new Collection;
        }

        return PlatformMessage::query()
            ->forTenant($tenantId)
            ->topLevel()
            ->orderByRaw('is_read ASC')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function viewThread(int $messageId): void
    {
        $message = $this->findOwnMessage($messageId);

        if (! $message instanceof PlatformMessage) {
            $this->viewingMessage = null;

            return;
        }

        $this->viewingMessage = $message->id;

        if (! $message->is_read && $message->sender_type === PlatformSenderType::Admin) {
            $message->update(['is_read' => true, 'read_at' => now()]);
        }
    }

    /** @return Collection<int, PlatformMessage>|null */
    public function getThread(): ?Collection
    {
        $parent = $this->getViewingRecord();

        if (! $parent instanceof PlatformMessage) {
            return null;
        }

        return $parent->replies()->oldest()->get();
    }

    public function getViewingRecord(): ?PlatformMessage
    {
        if ($this->viewingMessage === null) {
            return null;
        }

        return $this->findOwnMessage($this->viewingMessage);
    }

    public function sendReply(): void
    {
        $this->validate(['replyBody' => ['required', 'string', 'min:1']]);

        $parent = $this->getViewingRecord();

        if (! $parent instanceof PlatformMessage) {
            return;
        }

        PlatformMessage::query()->create([
            'tenant_id' => $parent->tenant_id,
            'sender_type' => PlatformSenderType::Tenant,
            'subject' => "Re: {$parent->subject}",
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
        $tenantId = tenant('id');

        if (! is_string($tenantId)) {
            return null;
        }

        $count = cache()->remember("navigation-badge:platform-messages:{$tenantId}:unread-admin", 60, fn (): int => PlatformMessage::query()
            ->forTenant($tenantId)
            ->fromAdmin()
            ->topLevel()
            ->unread()
            ->count());

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    /**
     * Looks a top-level message up inside the current bakery only, so another
     * bakery's message id behaves as if it did not exist.
     */
    private function findOwnMessage(int $messageId): ?PlatformMessage
    {
        $tenantId = tenant('id');

        if (! is_string($tenantId)) {
            return null;
        }

        return PlatformMessage::query()
            ->forTenant($tenantId)
            ->topLevel()
            ->find($messageId);
    }
}
