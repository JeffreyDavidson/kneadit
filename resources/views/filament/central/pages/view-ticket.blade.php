<x-filament-panels::page>
    <div style="max-width: 900px; margin: 0 auto;">
        {{-- Ticket Header --}}
        <div style="background: #2a1f18; border: 1px solid #3d2c1e; border-radius: 12px; padding: 24px; margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
                <div>
                    <h2 style="color: #faf0d6; font-size: 1.4rem; font-weight: 700; margin: 0 0 8px 0;">
                        {{ $record->subject }}
                    </h2>
                    <p style="color: #e8b04a; margin: 0; font-size: 0.9rem;">
                        From <strong>{{ $record->tenant?->name ?? $record->tenant_id }}</strong>
                        · {{ $record->created_at->diffForHumans() }}
                    </p>
                </div>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    @php
                        $statusColors = [
                            'open' => ['bg' => '#991b1b', 'text' => '#fca5a5'],
                            'in_progress' => ['bg' => '#92400e', 'text' => '#fde68a'],
                            'resolved' => ['bg' => '#166534', 'text' => '#86efac'],
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
                    <span style="background: {{ $sc['bg'] }}; color: {{ $sc['text'] }}; padding: 4px 12px; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">
                        {{ str_replace('_', ' ', $record->status) }}
                    </span>
                    <span style="background: {{ $pc['bg'] }}; color: {{ $pc['text'] }}; padding: 4px 12px; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">
                        {{ $record->priority }}
                    </span>
                </div>
            </div>

            <div style="margin-top: 16px; padding: 16px; background: #1c1410; border-radius: 8px; color: #f5d88e; line-height: 1.6; white-space: pre-wrap;">{{ $record->body }}</div>
        </div>

        {{-- Status Actions --}}
        @if($record->status !== 'closed')
        <div style="display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap;">
            @if($record->status === 'open')
                <button wire:click="updateStatus('in_progress')" style="background: #92400e; color: #fde68a; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.85rem;">
                    Mark In Progress
                </button>
            @endif
            @if(in_array($record->status, ['open', 'in_progress']))
                <button wire:click="updateStatus('resolved')" style="background: #166534; color: #86efac; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.85rem;">
                    Mark Resolved
                </button>
            @endif
            <button wire:click="updateStatus('closed')" style="background: #374151; color: #d1d5db; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.85rem;">
                Close Ticket
            </button>
        </div>
        @endif

        {{-- Replies Thread --}}
        <div style="margin-bottom: 24px;">
            <h3 style="color: #e8b04a; font-size: 1.1rem; font-weight: 600; margin-bottom: 16px;">
                Replies ({{ $record->replies->count() }})
            </h3>

            @forelse($record->replies->sortBy('created_at') as $reply)
                <div style="background: {{ $reply->author_type === 'admin' ? '#1c1410' : '#2a1f18' }}; border: 1px solid #3d2c1e; border-radius: 10px; padding: 16px; margin-bottom: 12px; {{ $reply->author_type === 'admin' ? 'border-left: 3px solid #d4920c;' : '' }}">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <strong style="color: #faf0d6;">{{ $reply->author_name }}</strong>
                            <span style="background: {{ $reply->author_type === 'admin' ? '#d4920c' : '#3d2c1e' }}; color: {{ $reply->author_type === 'admin' ? '#1c1410' : '#f5d88e' }}; padding: 2px 8px; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase;">
                                {{ $reply->author_type }}
                            </span>
                        </div>
                        <span style="color: #8a7a6a; font-size: 0.8rem;">{{ $reply->created_at->diffForHumans() }}</span>
                    </div>
                    <div style="color: #f5d88e; line-height: 1.6; white-space: pre-wrap;">{{ $reply->body }}</div>
                </div>
            @empty
                <div style="text-align: center; padding: 32px; color: #8a7a6a; font-style: italic;">
                    No replies yet.
                </div>
            @endforelse
        </div>

        {{-- Reply Form --}}
        @if($record->status !== 'closed')
        <div style="background: #2a1f18; border: 1px solid #3d2c1e; border-radius: 12px; padding: 20px;">
            <h3 style="color: #e8b04a; font-size: 1rem; font-weight: 600; margin: 0 0 12px 0;">Add Reply</h3>
            <form wire:submit="addReply">
                <textarea
                    wire:model="replyBody"
                    rows="4"
                    placeholder="Write your reply..."
                    style="width: 100%; background: #1c1410; border: 1px solid #3d2c1e; border-radius: 8px; padding: 12px; color: #faf0d6; font-size: 0.9rem; resize: vertical; box-sizing: border-box; font-family: inherit;"
                ></textarea>
                @error('replyBody')
                    <p style="color: #fca5a5; font-size: 0.8rem; margin: 4px 0;">{{ $message }}</p>
                @enderror
                <div style="margin-top: 12px; text-align: right;">
                    <button type="submit" style="background: #d4920c; color: #1c1410; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 0.9rem;">
                        Send Reply
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
</x-filament-panels::page>
