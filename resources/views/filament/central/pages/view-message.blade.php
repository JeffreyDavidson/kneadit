<x-filament-panels::page>
    <div class="space-y-4">
        {{-- Original message --}}
        <div class="rounded-xl border p-4" style="background-color: {{ $record->sender_type === 'admin' ? '#2a1f18' : '#1c1410' }}; border-color: #d4920c;">
            <div class="flex items-center justify-between mb-2">
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                      style="background-color: {{ $record->sender_type === 'admin' ? '#d4920c' : '#e8b04a' }}; color: #1c1410;">
                    {{ $record->sender_type === 'admin' ? '🛡️ Admin' : '🏪 ' . $record->tenant->name }}
                </span>
                <span class="text-xs" style="color: #f5d88e;">{{ $record->created_at->diffForHumans() }}</span>
            </div>
            <div class="prose prose-sm max-w-none" style="color: #f5d88e;">
                {!! nl2br(e($record->body)) !!}
            </div>
        </div>

        {{-- Thread replies --}}
        @foreach ($this->getThread() as $reply)
            <div class="rounded-xl border p-4 ml-6" style="background-color: {{ $reply->sender_type === 'admin' ? '#2a1f18' : '#1c1410' }}; border-color: {{ $reply->sender_type === 'admin' ? '#d4920c' : '#e8b04a' }};">
                <div class="flex items-center justify-between mb-2">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                          style="background-color: {{ $reply->sender_type === 'admin' ? '#d4920c' : '#e8b04a' }}; color: #1c1410;">
                        {{ $reply->sender_type === 'admin' ? '🛡️ Admin' : '🏪 ' . $record->tenant->name }}
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
</x-filament-panels::page>
