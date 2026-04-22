<x-filament-panels::page>
    <div class="max-w-[800px] mx-auto">
        {{-- Original message --}}
        @php $isAdmin = $record->sender_type === 'admin'; @endphp
        <div class="flex {{ $isAdmin ? 'justify-end' : 'justify-start' }} mb-6">
            <div @class([
                'max-w-[75%] border rounded-xl p-6',
                'bg-espresso border-honey/30 border-r-[3px] border-r-honey' => $isAdmin,
                'bg-warm-black border-honey/12' => ! $isAdmin,
            ])>
                <div class="flex items-center justify-between mb-3">
                    <span @class([
                        'px-2.5 py-1 rounded-full text-[0.65rem] font-semibold uppercase tracking-[0.1em]',
                        'bg-honey text-warm-black' => $isAdmin,
                        'bg-espresso text-golden' => ! $isAdmin,
                    ])>
                        {{ $isAdmin ? 'Admin' : $record->tenant->name }}
                    </span>
                    <span class="text-cinnamon text-xs">{{ $record->created_at->diffForHumans() }}</span>
                </div>
                <div class="text-parchment leading-relaxed">
                    {!! nl2br(e($record->body)) !!}
                </div>
            </div>
        </div>

        {{-- Thread replies --}}
        @foreach ($this->getThread() as $reply)
            @php $isReplyAdmin = $reply->sender_type === 'admin'; @endphp
            <div class="flex {{ $isReplyAdmin ? 'justify-end' : 'justify-start' }} mb-4">
                <div @class([
                    'max-w-[75%] border rounded-xl p-5',
                    'bg-espresso border-honey/30 border-r-[3px] border-r-honey' => $isReplyAdmin,
                    'bg-warm-black border-honey/12' => ! $isReplyAdmin,
                ])>
                    <div class="flex items-center justify-between mb-2">
                        <span @class([
                            'px-2.5 py-1 rounded-full text-[0.65rem] font-semibold uppercase tracking-[0.1em]',
                            'bg-honey text-warm-black' => $isReplyAdmin,
                            'bg-espresso text-golden' => ! $isReplyAdmin,
                        ])>
                            {{ $isReplyAdmin ? 'Admin' : $record->tenant->name }}
                        </span>
                        <span class="text-cinnamon text-xs">{{ $reply->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="text-parchment leading-relaxed">
                        {!! nl2br(e($reply->body)) !!}
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Reply form --}}
        <x-central.card title="Reply" class="mt-6">
            <form wire:submit="sendReply">
                <x-central.textarea wire:model="replyBody" rows="4" placeholder="Type your reply..." />
                @error('replyBody')
                    <p class="text-red-500 text-[0.8rem] mt-1">{{ $message }}</p>
                @enderror
                <div class="mt-3 text-right">
                    <x-central.button type="submit">Send Reply</x-central.button>
                </div>
            </form>
        </x-central.card>
    </div>
</x-filament-panels::page>
