<x-filament-panels::page>
    @if ($viewingMessage && $this->getViewingRecord())
        @php $record = $this->getViewingRecord(); @endphp

        <div class="mb-4">
            <button wire:click="backToList" class="text-sm flex items-center gap-1" style="color: #e8b04a;">
                <x-heroicon-o-arrow-left class="h-4 w-4" />
                Back to messages
            </button>
        </div>

        <h2 class="text-lg font-bold mb-4" style="color: #e8b04a;">{{ $record->subject }}</h2>

        <div class="space-y-4">
            {{-- Original --}}
            <div class="rounded-xl border p-4" style="background-color: {{ $record->sender_type === 'admin' ? '#2a1f18' : '#1c1410' }}; border-color: #d4920c;">
                <div class="flex items-center justify-between mb-2">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                          style="background-color: {{ $record->sender_type === 'admin' ? '#d4920c' : '#e8b04a' }}; color: #1c1410;">
                        {{ $record->sender_type === 'admin' ? '🛡️ KneadIt Team' : '🏪 You' }}
                    </span>
                    <span class="text-xs" style="color: #f5d88e;">{{ $record->created_at->diffForHumans() }}</span>
                </div>
                <div class="prose prose-sm max-w-none" style="color: #f5d88e;">
                    {!! nl2br(e($record->body)) !!}
                </div>
            </div>

            {{-- Replies --}}
            @foreach ($this->getThread() as $reply)
                <div class="rounded-xl border p-4 ml-6" style="background-color: {{ $reply->sender_type === 'admin' ? '#2a1f18' : '#1c1410' }}; border-color: {{ $reply->sender_type === 'admin' ? '#d4920c' : '#e8b04a' }};">
                    <div class="flex items-center justify-between mb-2">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                              style="background-color: {{ $reply->sender_type === 'admin' ? '#d4920c' : '#e8b04a' }}; color: #1c1410;">
                            {{ $reply->sender_type === 'admin' ? '🛡️ KneadIt Team' : '🏪 You' }}
                        </span>
                        <span class="text-xs" style="color: #f5d88e;">{{ $reply->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="prose prose-sm max-w-none" style="color: #f5d88e;">
                        {!! nl2br(e($reply->body)) !!}
                    </div>
                </div>
            @endforeach

            {{-- Reply form --}}
            <div class="rounded-xl border p-4" style="background-color: #2a1f18; border-color: #d4920c;">
                <h3 class="text-sm font-semibold mb-2" style="color: #e8b04a;">Reply</h3>
                <form wire:submit="sendReply">
                    <textarea
                        wire:model="replyBody"
                        rows="4"
                        class="w-full rounded-lg border p-3 text-sm"
                        style="background-color: #1c1410; border-color: #d4920c; color: #f5d88e;"
                        placeholder="Type your reply..."
                    ></textarea>
                    @error('replyBody')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <div class="mt-2 flex justify-end">
                        <button type="submit" class="rounded-lg px-4 py-2 text-sm font-medium" style="background-color: #d4920c; color: #1c1410;">
                            Send Reply
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="space-y-3">
            @forelse ($this->getMessages() as $msg)
                <div
                    wire:click="viewThread({{ $msg->id }})"
                    class="cursor-pointer rounded-xl border p-4 transition hover:opacity-90"
                    style="background-color: {{ $msg->is_read ? '#1c1410' : '#2a1f18' }}; border-color: {{ $msg->is_read ? '#e8b04a33' : '#d4920c' }};"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            @unless ($msg->is_read)
                                <span class="h-2 w-2 rounded-full" style="background-color: #d4920c;"></span>
                            @endunless
                            <div>
                                <p class="text-sm {{ $msg->is_read ? '' : 'font-bold' }}" style="color: #e8b04a;">
                                    {{ $msg->subject }}
                                </p>
                                <p class="text-xs mt-0.5" style="color: #f5d88e;">{{ Str::limit($msg->body, 80) }}</p>
                            </div>
                        </div>
                        <span class="text-xs whitespace-nowrap" style="color: #f5d88e;">{{ $msg->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            @empty
                <div class="text-center py-8" style="color: #f5d88e;">
                    <x-heroicon-o-envelope class="mx-auto h-12 w-12 mb-2" style="color: #d4920c;" />
                    <p>No messages yet</p>
                </div>
            @endforelse
        </div>
    @endif
</x-filament-panels::page>
