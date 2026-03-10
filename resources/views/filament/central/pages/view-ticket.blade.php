<x-filament-panels::page>
    <div style="max-width: 900px; margin: 0 auto;">
        {{-- Ticket Header --}}
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
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
                        $sc = $statusColors[$record->status] ?? $statusColors['open'];
                        $pc = $priorityColors[$record->priority] ?? $priorityColors['normal'];
                    @endphp
                    <span style="background: {{ $sc['bg'] }}; color: {{ $sc['text'] }}; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em;">
                        {{ str_replace('_', ' ', $record->status) }}
                    </span>
                    <span style="background: {{ $pc['bg'] }}; color: {{ $pc['text'] }}; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em;">
                        {{ $record->priority }}
                    </span>
                </div>
            </div>

            <div style="margin-top: 1rem; border-top: 1px solid rgba(212,146,12,0.08); padding-top: 1rem; color: #faf0d6; line-height: 1.6; white-space: pre-wrap;">{{ $record->body }}</div>
        </div>

        {{-- Status Actions --}}
        @if($record->status !== 'closed')
        <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
            @if($record->status === 'open')
                <button wire:click="updateStatus('in_progress')" style="background: #92400e; color: #fde68a; border: none; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.85rem;">
                    Mark In Progress
                </button>
            @endif
            @if(in_array($record->status, ['open', 'in_progress']))
                <button wire:click="updateStatus('resolved')" style="background: #065f46; color: #6ee7b7; border: none; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.85rem;">
                    Mark Resolved
                </button>
            @endif
            <button wire:click="updateStatus('closed')" style="background: #374151; color: #d1d5db; border: none; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.85rem;">
                Close Ticket
            </button>
        </div>
        @endif

        {{-- Replies Thread --}}
        <div style="margin-bottom: 1.5rem;">
            <div style="color: #d4920c; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-bottom: 1rem;">
                Replies ({{ $record->replies->count() }})
            </div>

            @forelse($record->replies->sortBy('created_at') as $reply)
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
        @if($record->status !== 'closed')
        <div style="background: #1c1410; border: 1px solid rgba(212,146,12,0.12); border-radius: 12px; padding: 1.5rem;">
            <div style="color: white; font-weight: 700; font-size: 1rem; margin-bottom: 1rem;">Add Reply</div>
            <form wire:submit="addReply">
                <textarea
                    wire:model="replyBody"
                    rows="4"
                    placeholder="Write your reply..."
                    style="width: 100%; background: #2a1f18; border: 1px solid rgba(212,146,12,0.12); border-radius: 8px; padding: 0.75rem; color: #faf0d6; font-size: 0.9rem; resize: vertical; box-sizing: border-box; font-family: inherit; outline: none;"
                    onfocus="this.style.borderColor='#d4920c'"
                    onblur="this.style.borderColor='rgba(212,146,12,0.12)'"
                ></textarea>
                @error('replyBody')
                    <p style="color: #ef4444; font-size: 0.8rem; margin: 0.25rem 0 0 0;">{{ $message }}</p>
                @enderror
                <div style="margin-top: 0.75rem; text-align: right;">
                    <button type="submit" style="background: #d4920c; color: #1c1410; border: none; padding: 0.6rem 1.5rem; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 0.9rem;">
                        Send Reply
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
</x-filament-panels::page>
