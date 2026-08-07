<x-filament-panels::page>
    @if ($viewingMessage && $this->getViewingRecord())
        @php $record = $this->getViewingRecord(); @endphp

        <div class="mb-4">
            <button wire:click="backToList" class="text-golden flex items-center gap-1 text-sm">
                <x-heroicon-o-arrow-left class="h-4 w-4" />
                Back to messages
            </button>
        </div>

        <h2 class="text-golden mb-4 text-lg font-bold">{{ $record->subject }}</h2>

        <div class="space-y-4">
            {{-- Original --}}
            <div @class([
                'rounded-xl border border-honey p-4',
                'bg-espresso' => $record->sender_type === 'admin',
                'bg-warm-black' => $record->sender_type !== 'admin',
            ])>
                <div class="mb-2 flex items-center justify-between">
                    <span @class([
                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium text-warm-black',
                        'bg-honey' => $record->sender_type === 'admin',
                        'bg-golden' => $record->sender_type !== 'admin',
                    ])>
                        <x-filament::icon
                            :icon="$record->sender_type === 'admin' ? 'heroicon-o-shield-check' : 'heroicon-o-building-storefront'"
                            class="h-3.5 w-3.5"
                        />
                        {{ $record->sender_type === 'admin' ? 'KneadIt Team' : 'You' }}
                    </span>
                    <span class="text-butter text-xs">{{ $record->created_at->diffForHumans() }}</span>
                </div>
                <div class="prose prose-sm text-butter max-w-none">{!! nl2br(e($record->body)) !!}</div>
            </div>

            {{-- Replies --}}
            @foreach ($this->getThread() as $reply)
                <div @class([
                    'rounded-xl border p-4 ml-6',
                    'bg-espresso border-honey' => $reply->sender_type === 'admin',
                    'bg-warm-black border-golden' => $reply->sender_type !== 'admin',
                ])>
                    <div class="mb-2 flex items-center justify-between">
                        <span @class([
                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium text-warm-black',
                            'bg-honey' => $reply->sender_type === 'admin',
                            'bg-golden' => $reply->sender_type !== 'admin',
                        ])>
                            <x-filament::icon
                                :icon="$reply->sender_type === 'admin' ? 'heroicon-o-shield-check' : 'heroicon-o-building-storefront'"
                                class="h-3.5 w-3.5"
                            />
                            {{ $reply->sender_type === 'admin' ? 'KneadIt Team' : 'You' }}
                        </span>
                        <span class="text-butter text-xs">{{ $reply->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="prose prose-sm text-butter max-w-none">{!! nl2br(e($reply->body)) !!}</div>
                </div>
            @endforeach

            {{-- Reply form --}}
            <div class="border-honey bg-espresso rounded-xl border p-4">
                <h3 class="text-golden mb-2 text-sm font-semibold">Reply</h3>
                <form wire:submit="sendReply">
                    <textarea
                        wire:model="replyBody"
                        rows="4"
                        placeholder="Type your reply..."
                        class="border-honey bg-warm-black text-butter w-full rounded-lg border p-3 text-sm"
                    ></textarea>
                    @error('replyBody')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                    <div class="mt-2 flex justify-end">
                        <x-central.button type="submit" size="sm"> Send Reply </x-central.button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="space-y-3">
            @forelse ($this->getMessages() as $msg)
                <div
                    wire:click="viewThread({{ $msg->id }})"
                    @class([
                        'cursor-pointer rounded-xl border p-4 transition hover:opacity-90',
                        'bg-warm-black border-golden/20' => $msg->is_read,
                        'bg-espresso border-honey' => ! $msg->is_read,
                    ])
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            @unless ($msg->is_read)
                                <span class="bg-honey h-2 w-2 rounded-full"></span>
                            @endunless
                            <div>
                                <p class="text-sm text-golden {{ $msg->is_read ? '' : 'font-bold' }}">
                                    {{ $msg->subject }}
                                </p>
                                <p class="text-butter mt-0.5 text-xs">{{ Str::limit($msg->body, 80) }}</p>
                            </div>
                        </div>
                        <span class="text-butter text-xs whitespace-nowrap">{{ $msg->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            @empty
                <div class="text-butter py-8 text-center">
                    <x-heroicon-o-envelope class="text-honey mx-auto mb-2 h-12 w-12" />
                    <p>No messages yet</p>
                </div>
            @endforelse
        </div>
    @endif
</x-filament-panels::page>
