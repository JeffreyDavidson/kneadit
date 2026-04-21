<x-filament-panels::page>
    @if ($viewingMessage && $this->getViewingRecord())
        @php $record = $this->getViewingRecord(); @endphp

        <div class="mb-4">
            <button wire:click="backToList" class="text-sm flex items-center gap-1 text-[#e8b04a]">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                Back to messages
            </button>
        </div>

        <h2 class="text-lg font-bold mb-4 text-[#e8b04a]">{{ $record->subject }}</h2>

        <div class="space-y-4">
            {{-- Original --}}
            <div @class([
                'rounded-xl border border-[#d4920c] p-4',
                'bg-[#2a1f18]' => $record->sender_type === 'admin',
                'bg-[#1c1410]' => $record->sender_type !== 'admin',
            ])>
                <div class="flex items-center justify-between mb-2">
                    <span @class([
                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium text-[#1c1410]',
                        'bg-[#d4920c]' => $record->sender_type === 'admin',
                        'bg-[#e8b04a]' => $record->sender_type !== 'admin',
                    ])>
                        {{ $record->sender_type === 'admin' ? '🛡️ KneadIt Team' : '🏪 You' }}
                    </span>
                    <span class="text-xs text-[#f5d88e]">{{ $record->created_at->diffForHumans() }}</span>
                </div>
                <div class="prose prose-sm max-w-none text-[#f5d88e]">
                    {!! nl2br(e($record->body)) !!}
                </div>
            </div>

            {{-- Replies --}}
            @foreach ($this->getThread() as $reply)
                <div @class([
                    'rounded-xl border p-4 ml-6',
                    'bg-[#2a1f18] border-[#d4920c]' => $reply->sender_type === 'admin',
                    'bg-[#1c1410] border-[#e8b04a]' => $reply->sender_type !== 'admin',
                ])>
                    <div class="flex items-center justify-between mb-2">
                        <span @class([
                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium text-[#1c1410]',
                            'bg-[#d4920c]' => $reply->sender_type === 'admin',
                            'bg-[#e8b04a]' => $reply->sender_type !== 'admin',
                        ])>
                            {{ $reply->sender_type === 'admin' ? '🛡️ KneadIt Team' : '🏪 You' }}
                        </span>
                        <span class="text-xs text-[#f5d88e]">{{ $reply->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="prose prose-sm max-w-none text-[#f5d88e]">
                        {!! nl2br(e($reply->body)) !!}
                    </div>
                </div>
            @endforeach

            {{-- Reply form --}}
            <div class="rounded-xl border border-[#d4920c] bg-[#2a1f18] p-4">
                <h3 class="text-sm font-semibold mb-2 text-[#e8b04a]">Reply</h3>
                <form wire:submit="sendReply">
                    <textarea wire:model="replyBody" rows="4" placeholder="Type your reply..."
                              class="w-full rounded-lg border border-[#d4920c] bg-[#1c1410] p-3 text-sm text-[#f5d88e]"></textarea>
                    @error('replyBody')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <div class="mt-2 flex justify-end">
                        <button type="submit" class="rounded-lg px-4 py-2 text-sm font-medium bg-[#d4920c] text-[#1c1410]">
                            Send Reply
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="space-y-3">
            @forelse ($this->getMessages() as $msg)
                <div wire:click="viewThread({{ $msg->id }})"
                    @class([
                        'cursor-pointer rounded-xl border p-4 transition hover:opacity-90',
                        'bg-[#1c1410] border-[#e8b04a]/20' => $msg->is_read,
                        'bg-[#2a1f18] border-[#d4920c]' => ! $msg->is_read,
                    ])>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            @unless ($msg->is_read)
                                <span class="h-2 w-2 rounded-full bg-[#d4920c]"></span>
                            @endunless
                            <div>
                                <p class="text-sm text-[#e8b04a] {{ $msg->is_read ? '' : 'font-bold' }}">
                                    {{ $msg->subject }}
                                </p>
                                <p class="text-xs mt-0.5 text-[#f5d88e]">{{ Str::limit($msg->body, 80) }}</p>
                            </div>
                        </div>
                        <span class="text-xs whitespace-nowrap text-[#f5d88e]">{{ $msg->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-[#f5d88e]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mx-auto mb-2 w-12 h-12 text-[#d4920c]"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                    <p>No messages yet</p>
                </div>
            @endforelse
        </div>
    @endif
</x-filament-panels::page>
