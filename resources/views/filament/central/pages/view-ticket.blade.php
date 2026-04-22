<x-filament-panels::page>
    <div class="max-w-[900px] mx-auto">
        {{-- Ticket Header --}}
        <x-central.card class="mb-6">
            <div class="flex justify-between items-start flex-wrap gap-3">
                <div>
                    <div class="text-white font-bold text-[1.25rem] m-0 mb-2">
                        {{ $record->subject }}
                    </div>
                    <div class="text-cinnamon text-[0.85rem]">
                        From <span class="text-golden font-semibold">{{ $record->tenant?->name ?? $record->tenant_id }}</span>
                        · {{ $record->created_at->diffForHumans() }}
                    </div>
                </div>
                <div class="flex gap-2 flex-wrap">
                    @php
                        $statusColors = [
                            'open' => 'bg-red-900 text-red-300',
                            'in_progress' => 'bg-amber-800 text-amber-200',
                            'resolved' => 'bg-emerald-800 text-emerald-300',
                            'closed' => 'bg-gray-700 text-gray-300',
                        ];
                        $priorityColors = [
                            'high' => 'bg-red-900 text-red-300',
                            'normal' => 'bg-blue-900 text-blue-300',
                            'low' => 'bg-gray-700 text-gray-300',
                        ];
                        $statusValue = $record->status instanceof \BackedEnum ? $record->status->value : $record->status;
                        $priorityValue = $record->priority instanceof \BackedEnum ? $record->priority->value : $record->priority;
                        $statusClass = $statusColors[$statusValue] ?? $statusColors['open'];
                        $priorityClass = $priorityColors[$priorityValue] ?? $priorityColors['normal'];
                    @endphp
                    <span class="{{ $statusClass }} px-3 py-1 rounded-full text-[0.7rem] font-semibold uppercase tracking-[0.1em]">
                        {{ str_replace('_', ' ', $statusValue) }}
                    </span>
                    <span class="{{ $priorityClass }} px-3 py-1 rounded-full text-[0.7rem] font-semibold uppercase tracking-[0.1em]">
                        {{ $record->priority }}
                    </span>
                </div>
            </div>

            <div class="mt-4 border-t border-honey/8 pt-4 text-parchment leading-relaxed whitespace-pre-wrap">{{ $record->body }}</div>
        </x-central.card>

        {{-- Status Actions --}}
        @if ($record->status !== 'closed')
        <div class="flex gap-2 mb-6 flex-wrap">
            @if ($record->status === 'open')
                <x-central.button variant="warning" size="sm" wire:click="updateStatus('in_progress')">Mark In Progress</x-central.button>
            @endif
            @if (in_array($record->status, ['open', 'in_progress']))
                <x-central.button variant="success" size="sm" wire:click="updateStatus('resolved')">Mark Resolved</x-central.button>
            @endif
            <x-central.button variant="neutral" size="sm" wire:click="updateStatus('closed')">Close Ticket</x-central.button>
        </div>
        @endif

        {{-- Replies Thread --}}
        <div class="mb-6">
            <x-central.eyebrow class="mb-4">Replies ({{ $record->replies->count() }})</x-central.eyebrow>

            @forelse ($record->replies->sortBy('created_at') as $reply)
                @php $isAdmin = $reply->author_type === 'admin'; @endphp
                <div class="flex {{ $isAdmin ? 'justify-end' : 'justify-start' }} mb-3">
                    <div @class([
                        'max-w-[80%] border border-honey/12 rounded-xl p-4',
                        'bg-espresso border-r-[3px] border-r-honey' => $isAdmin,
                        'bg-warm-black' => ! $isAdmin,
                    ])>
                        <div class="flex justify-between items-center mb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-parchment font-semibold text-[0.85rem]">{{ $reply->author_name }}</span>
                                <span @class([
                                    'px-2 py-0.5 rounded-full text-[0.6rem] font-semibold uppercase tracking-[0.1em]',
                                    'bg-honey text-warm-black' => $isAdmin,
                                    'bg-espresso text-golden' => ! $isAdmin,
                                ])>
                                    {{ $reply->author_type }}
                                </span>
                            </div>
                            <span class="text-cinnamon text-xs">{{ $reply->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-parchment leading-relaxed whitespace-pre-wrap">{{ $reply->body }}</div>
                    </div>
                </div>
            @empty
                <div class="text-center p-8 text-cinnamon italic">
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
