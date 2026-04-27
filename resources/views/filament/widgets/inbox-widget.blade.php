@php $count = $this->getUnreadCount(); @endphp

<div>
    @if ($count > 0)
        <x-admin.dashboard.preview-card heading="Inbox" icon="📬">
            <a href="{{ $this->getMessagesUrl() }}" style="text-decoration: none; color: inherit; display: block;">
                <x-admin.dashboard.stat-row label="Unread messages" :value="$count" />
                <div style="margin-top: 6px; font-size: 0.65rem; color: var(--pw-card-text-muted);">
                    {{ $count > 1 ? 'New messages from KneadIt' : 'New message from KneadIt' }} — tap to view →
                </div>
            </a>
        </x-admin.dashboard.preview-card>
    @endif
</div>
