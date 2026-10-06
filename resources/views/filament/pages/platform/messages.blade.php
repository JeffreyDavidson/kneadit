@use('App\Enums\Platform\PlatformSenderType')

<x-filament-panels::page>
    @if ($viewingMessage && $this->getViewingRecord())
        @php $record = $this->getViewingRecord(); @endphp

        <div class="mb-4">
            <button wire:click="backToList" class="flex items-center gap-1 text-sm text-(--kn-honey-text)">
                <x-heroicon-o-arrow-left class="h-4 w-4" />
                Back to messages
            </button>
        </div>

        <h2 class="mb-4 text-lg font-bold text-(--kn-honey-text)">{{ $record->subject }}</h2>

        <div class="space-y-4">
            {{-- Original --}}
            <div @class([
                'rounded-xl border border-(--kn-honey) p-4',
                'bg-(--kn-surface-sunken)' => $record->sender_type === PlatformSenderType::Admin,
                'bg-(--kn-surface)' => $record->sender_type !== PlatformSenderType::Admin,
            ])>
                <div class="mb-2 flex items-center justify-between">
                    <span @class([
                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium text-(--kn-on-honey)',
                        'bg-(--kn-honey)' => $record->sender_type === PlatformSenderType::Admin,
                        'bg-(--kn-honey-hover)' => $record->sender_type !== PlatformSenderType::Admin,
                    ])>
                        <x-filament::icon :icon="$record->sender_type->inboxIcon()" class="h-3.5 w-3.5" />
                        {{ $record->sender_type->inboxLabel() }}
                    </span>
                    <span class="text-xs text-(--kn-ink-2)">{{ $record->created_at->diffForHumans() }}</span>
                </div>
                <div class="prose prose-sm max-w-none text-(--kn-ink-2)">{!! nl2br(e($record->body)) !!}</div>
            </div>

            {{-- Replies --}}
            @foreach ($this->getThread() as $reply)
                <div @class([
                    'rounded-xl border p-4 ml-6',
                    'bg-(--kn-surface-sunken) border-(--kn-honey)' => $reply->sender_type === PlatformSenderType::Admin,
                    'bg-(--kn-surface) border-(--kn-honey-hover)' => $reply->sender_type !== PlatformSenderType::Admin,
                ])>
                    <div class="mb-2 flex items-center justify-between">
                        <span @class([
                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium text-(--kn-on-honey)',
                            'bg-(--kn-honey)' => $reply->sender_type === PlatformSenderType::Admin,
                            'bg-(--kn-honey-hover)' => $reply->sender_type !== PlatformSenderType::Admin,
                        ])>
                            <x-filament::icon :icon="$reply->sender_type->inboxIcon()" class="h-3.5 w-3.5" />
                            {{ $reply->sender_type->inboxLabel() }}
                        </span>
                        <span class="text-xs text-(--kn-ink-2)">{{ $reply->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="prose prose-sm max-w-none text-(--kn-ink-2)">{!! nl2br(e($reply->body)) !!}</div>
                </div>
            @endforeach

            {{-- Reply form --}}
            <div class="rounded-xl border border-(--kn-honey) bg-(--kn-surface-sunken) p-4">
                <h3 class="mb-2 text-sm font-semibold text-(--kn-honey-text)">Reply</h3>
                <form wire:submit="sendReply">
                    <textarea
                        wire:model="replyBody"
                        rows="4"
                        placeholder="Type your reply..."
                        class="w-full rounded-lg border border-(--kn-honey) bg-(--kn-surface) p-3 text-sm text-(--kn-ink-2)"
                    ></textarea>
                    @error('replyBody')
                        <p class="mt-1 text-xs text-(--kn-danger)">{{ $message }}</p>
                    @enderror
                    <div class="mt-2 flex justify-end">
                        <x-central.button type="submit" size="sm"> Send Reply </x-central.button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="space-y-3">
            @forelse ($this->inboxMessages() as $msg)
                <div
                    wire:click="viewThread({{ $msg->id }})"
                    @class([
                        'cursor-pointer rounded-xl border p-4 transition hover:opacity-90',
                        'bg-(--kn-surface) border-(--kn-border)' => $msg->is_read,
                        'bg-(--kn-surface-sunken) border-(--kn-honey)' => ! $msg->is_read,
                    ])
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            @unless ($msg->is_read)
                                <span class="h-2 w-2 rounded-full bg-(--kn-honey)"></span>
                            @endunless
                            <div>
                                <p class="text-sm text-(--kn-honey-text) {{ $msg->is_read ? '' : 'font-bold' }}">
                                    {{ $msg->subject }}
                                </p>
                                <p class="mt-0.5 text-xs text-(--kn-ink-2)">{{ Str::limit($msg->body, 80) }}</p>
                            </div>
                        </div>
                        <span class="text-xs whitespace-nowrap text-(--kn-ink-2)">{{ $msg->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-(--kn-ink-2)">
                    <x-heroicon-o-envelope class="mx-auto mb-2 h-12 w-12 text-(--kn-honey-text)" />
                    <p>No messages yet</p>
                </div>
            @endforelse
        </div>
    @endif
</x-filament-panels::page>
