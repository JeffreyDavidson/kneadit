<x-filament-panels::page>
    <div style="max-width: 900px; margin: 0 auto;">
        {{-- Ticket Header --}}
        <x-central.card class="mb-6">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
                <div>
                    <div style="color: white; font-weight: 700; font-size: 1.25rem; margin: 0 0 0.5rem 0;">
                        {{ $record->subject }}
                    </div>
                    <div style="color: #8b6844; font-size: 0.85rem;">
                        From <span style="color: #e8b04a; font-weight: 600;">{{ $record->tenant?->name ?? $record->tenant_id }}</span>
                        · {{ $record->created_at->diffForHumans() }}
                    </div>
                </div>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    @php
                        $statusColors = [
                            'open' => ['bg' => '#991b1b', 'text' => '#fca5a5'],
                            'in_progress' => ['bg' => '#92400e', 'text' => '#fde68a'],
                            'resolved' => ['bg' => '#065f46', 'text' => '#6ee7b7'],
                            'closed' => ['bg' => '#374151', 'text' => '#d1d5db'],
                        ];
                        $priorityColors = [
                            'high' => ['bg' => '#991b1b', 'text' => '#fca5a5'],
                            'normal' => ['bg' => '#1e3a5f', 'text' => '#93c5fd'],
                            'low' => ['bg' => '#374151', 'text' => '#d1d5db'],
                        ];
                        $sc = $statusColors[$record->status instanceof \BackedEnum ? $record->status->value : $record->status] ?? $statusColors['open'];
                        $pc = $priorityColors[$record->priority instanceof \BackedEnum ? $record->priority->value : $record->priority] ?? $priorityColors['normal'];
                    @endphp
                    <span style="background: {{ $sc['bg'] }}; color: {{ $sc['text'] }}; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em;">
                        {{ str_replace('_', ' ', $record->status instanceof \BackedEnum ? $record->status->value : $record->status) }}
                    </span>
                    <span style="background: {{ $pc['bg'] }}; color: {{ $pc['text'] }}; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em;">
                        {{ $record->priority }}
                    </span>
                </div>
            </div>

            <div class="mt-4 border-t border-honey/8 pt-4 text-parchment leading-relaxed whitespace-pre-wrap">{{ $record->body }}</div>
        </x-central.card>

        {{-- Status Actions --}}
        @if ($record->status !== 'closed')
        <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
            @if ($record->status === 'open')
                <x-central.button variant="warning" wire:click="updateStatus('in_progress')">Mark In Progress</x-central.button>
            @endif
            @if (in_array($record->status, ['open', 'in_progress']))
                <x-central.button variant="success" wire:click="updateStatus('resolved')">Mark Resolved</x-central.button>
            @endif
            <x-central.button variant="neutral" wire:click="updateStatus('closed')">Close Ticket</x-central.button>
        </div>
        @endif

        {{-- Replies Thread --}}
        <div style="margin-bottom: 1.5rem;">
            <x-central.eyebrow class="mb-4">Replies ({{ $record->replies->count() }})</x-central.eyebrow>

            @forelse ($record->replies->sortBy('created_at') as $reply)
                @php $isAdmin = $reply->author_type === 'admin'; @endphp
                <div style="display: flex; justify-content: {{ $isAdmin ? 'flex-end' : 'flex-start' }}; margin-bottom: 0.75rem;">
                    <div style="max-width: 80%; background: {{ $isAdmin ? '#2a1f18' : '#1c1410' }}; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1rem; {{ $isAdmin ? 'border-right: 3px solid #d4920c;' : '' }}">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="color: #faf0d6; font-weight: 600; font-size: 0.85rem;">{{ $reply->author_name }}</span>
                                <span style="background: {{ $isAdmin ? '#d4920c' : '#2a1f18' }}; color: {{ $isAdmin ? '#1c1410' : '#e8b04a' }}; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.6rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em;">
                                    {{ $reply->author_type }}
                                </span>
                            </div>
                            <span style="color: #8b6844; font-size: 0.75rem;">{{ $reply->created_at->diffForHumans() }}</span>
                        </div>
                        <div style="color: #faf0d6; line-height: 1.6; white-space: pre-wrap;">{{ $reply->body }}</div>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 2rem; color: #8b6844; font-style: italic;">
                    No replies yet.
                </div>
            @endforelse
        </div>

        {{-- Reply Form --}}
        @if ($record->status !== 'closed')
        <x-central.card title="Add Reply">
            <form wire:submit="addReply">
                <x-central.textarea wire:model="replyBody" rows="4" placeholder="Write your reply..." />
                @error('replyBody')
                    <p class="text-red-500 text-[0.8rem] mt-1">{{ $message }}</p>
                @enderror
                <div class="mt-3 text-right">
                    <x-central.button type="submit">Send Reply</x-central.button>
                </div>
            </form>
        </x-central.card>
        @endif
    </div>
</x-filament-panels::page>
