@php
    use App\Enums\Platform\SupportReplyAuthorType;
    use App\Enums\Platform\SupportTicketStatus;

    $statusValue = $record->status instanceof \BackedEnum ? $record->status->value : $record->status;
    $priorityValue = $record->priority instanceof \BackedEnum ? $record->priority->value : $record->priority;

    $statusTone = match ($statusValue) {
        'open' => ['bg' => 'bg-red-500/15', 'border' => 'border-red-500/25', 'text' => 'text-red-400', 'dot' => 'bg-red-500'],
        'in_progress' => ['bg' => 'bg-amber-500/15', 'border' => 'border-amber-500/25', 'text' => 'text-amber-400', 'dot' => 'bg-amber-500'],
        'resolved' => ['bg' => 'bg-emerald-500/15', 'border' => 'border-emerald-500/25', 'text' => 'text-emerald-400', 'dot' => 'bg-emerald-500'],
        'closed' => ['bg' => 'bg-cinnamon/15', 'border' => 'border-cinnamon/25', 'text' => 'text-cinnamon', 'dot' => 'bg-cinnamon'],
        default => ['bg' => 'bg-cinnamon/15', 'border' => 'border-cinnamon/25', 'text' => 'text-cinnamon', 'dot' => 'bg-cinnamon'],
    };

    $priorityTone = match ($priorityValue) {
        'high' => 'text-red-400',
        'normal' => 'text-sky-400',
        'low' => 'text-cinnamon',
        default => 'text-cinnamon',
    };

    $initials = function (string $name): string {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        return strtoupper(substr($parts[0] ?? '?', 0, 1) . substr($parts[1] ?? '', 0, 1));
    };
@endphp

<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] gap-6">
        {{-- ============== MAIN COLUMN ============== --}}
        <div class="space-y-8">
            {{-- Ticket Meta Strip (subject is handled by Filament's page heading) --}}
            <x-central.card padding="px-5 py-4">
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="shrink-0 w-10 h-10 rounded-xl bg-honey/15 border border-honey/25 flex items-center justify-center text-honey font-bold text-[0.85rem]">
                        {{ $initials($record->tenant?->name ?? 'T') }}
                    </div>
                    <div class="flex items-center gap-2 text-[0.8rem] text-cinnamon">
                        <span class="text-white font-semibold">{{ $record->tenant?->name ?? $record->tenant_id }}</span>
                        <span class="text-cinnamon/50">•</span>
                        <span>Ticket #{{ $record->id }}</span>
                        <span class="text-cinnamon/50">•</span>
                        <span>{{ $record->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </x-central.card>

            {{-- Conversation --}}
            <div class="space-y-6">
                <div class="flex items-center justify-between mb-1">
                    <x-central.eyebrow>Conversation</x-central.eyebrow>
                    <span class="text-cinnamon text-[0.75rem]">{{ $record->replies->count() + 1 }} {{ \Illuminate\Support\Str::plural('message', $record->replies->count() + 1) }}</span>
                </div>

                {{-- Original Message (always from tenant) --}}
                <div class="flex gap-3">
                    <div class="shrink-0 w-9 h-9 rounded-full bg-cinnamon/20 border border-cinnamon/25 flex items-center justify-center text-parchment font-bold text-[0.75rem]">
                        {{ $initials($record->tenant?->name ?? 'T') }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-2 flex-wrap">
                            <span class="text-white font-semibold text-[0.85rem]">{{ $record->tenant?->name ?? 'Customer' }}</span>
                            <span class="text-[0.6rem] uppercase tracking-[0.1em] font-bold text-cinnamon bg-espresso px-1.5 py-0.5 rounded">Customer</span>
                            <span class="text-cinnamon text-[0.75rem]">{{ $record->created_at->format('M j, Y · g:i A') }}</span>
                        </div>
                        <div class="rounded-xl border border-honey/12 bg-warm-black p-5">
                            <div class="text-parchment text-[0.9rem] leading-relaxed whitespace-pre-wrap">{{ $record->body }}</div>
                        </div>
                    </div>
                </div>

                @foreach ($record->replies->sortBy('created_at') as $reply)
                    @php
                        $replyType = $reply->author_type instanceof \BackedEnum ? $reply->author_type->value : $reply->author_type;
                        $isAdmin = $replyType === 'admin';
                    @endphp
                    <div class="flex gap-3">
                        <div @class([
                            'shrink-0 w-9 h-9 rounded-full flex items-center justify-center font-bold text-[0.75rem] border',
                            'bg-honey/15 border-honey/30 text-honey' => $isAdmin,
                            'bg-cinnamon/20 border-cinnamon/25 text-parchment' => ! $isAdmin,
                        ])>
                            {{ $initials($reply->author_name) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-2 flex-wrap">
                                <span class="text-white font-semibold text-[0.85rem]">{{ $reply->author_name }}</span>
                                <span @class([
                                    'text-[0.6rem] uppercase tracking-[0.1em] font-bold px-1.5 py-0.5 rounded',
                                    'bg-honey text-warm-black' => $isAdmin,
                                    'bg-espresso text-cinnamon' => ! $isAdmin,
                                ])>
                                    {{ $isAdmin ? 'Staff' : 'Customer' }}
                                </span>
                                <span class="text-cinnamon text-[0.75rem]">{{ $reply->created_at->format('M j, Y · g:i A') }}</span>
                            </div>
                            <div @class([
                                'rounded-xl border p-5',
                                'border-honey/25 bg-honey/5' => $isAdmin,
                                'border-honey/12 bg-warm-black' => ! $isAdmin,
                            ])>
                                <div class="text-parchment text-[0.9rem] leading-relaxed whitespace-pre-wrap">{{ $reply->body }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Reply Composer --}}
            @if ($statusValue !== SupportTicketStatus::Closed->value)
                <x-central.card>
                    <div class="flex items-center justify-between mb-3">
                        <x-central.eyebrow>Reply to customer</x-central.eyebrow>
                        <span class="inline-flex items-center gap-1.5 text-[0.7rem] text-cinnamon">
                            <x-heroicon-o-envelope class="w-3.5 h-3.5" />
                            Visible to {{ $record->tenant?->name ?? 'customer' }}
                        </span>
                    </div>
                    <form wire:submit="addReply">
                        <x-central.textarea wire:model="replyBody" rows="4" placeholder="Write your reply…" />
                        @error('replyBody')
                            <p class="text-red-500 text-[0.8rem] mt-1.5">{{ $message }}</p>
                        @enderror
                        <div class="mt-3 flex items-center justify-end gap-2">
                            <x-central.button type="submit" class="gap-1.5">
                                <x-heroicon-o-paper-airplane class="w-3.5 h-3.5" stroke-width="2.5" />
                                Send Reply
                            </x-central.button>
                        </div>
                    </form>
                </x-central.card>
            @else
                <div class="inline-flex items-center gap-2 text-[0.8rem] text-cinnamon">
                    <x-heroicon-o-lock-closed class="w-4 h-4" />
                    Replies disabled — reopen this ticket from the Status panel to continue the conversation.
                </div>
            @endif
        </div>

        {{-- ============== SIDEBAR ============== --}}
        <div class="space-y-6">
            {{-- Status Panel --}}
            <x-central.card>
                <x-central.eyebrow class="mb-3">Status</x-central.eyebrow>

                <div class="inline-flex items-center gap-2 {{ $statusTone['bg'] }} {{ $statusTone['border'] }} {{ $statusTone['text'] }} border rounded-full px-3 py-1.5 text-[0.75rem] font-bold uppercase tracking-[0.08em] mb-3">
                    <span class="w-1.5 h-1.5 rounded-full {{ $statusTone['dot'] }} @if ($statusValue === 'open' || $statusValue === 'in_progress') animate-pulse @endif"></span>
                    {{ str_replace('_', ' ', $statusValue) }}
                </div>

                <div class="text-[0.8rem] mb-4">
                    <span class="text-cinnamon">Priority:</span>
                    <span class="{{ $priorityTone }} font-semibold capitalize">{{ $priorityValue }}</span>
                </div>

                <div class="space-y-2">
                    @if ($statusValue === 'open')
                        <button type="button" wire:click="updateStatus('in_progress')"
                            class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-[0.8rem] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/25 hover:bg-amber-500/20 transition-colors cursor-pointer">
                            <x-heroicon-o-bolt class="w-3.5 h-3.5" />
                            Mark In Progress
                        </button>
                    @endif

                    @if (in_array($statusValue, ['open', 'in_progress'], true))
                        <button type="button" wire:click="updateStatus('resolved')"
                            class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-[0.8rem] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 hover:bg-emerald-500/20 transition-colors cursor-pointer">
                            <x-heroicon-o-check-circle class="w-3.5 h-3.5" />
                            Mark Resolved
                        </button>
                    @endif

                    @if ($statusValue !== 'closed')
                        <button type="button" wire:click="updateStatus('closed')"
                            class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-[0.8rem] font-semibold bg-cinnamon/10 text-cinnamon border border-cinnamon/25 hover:bg-cinnamon/20 transition-colors cursor-pointer">
                            <x-heroicon-o-archive-box class="w-3.5 h-3.5" />
                            Close Ticket
                        </button>
                    @else
                        <button type="button" wire:click="updateStatus('open')"
                            class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-[0.8rem] font-semibold bg-honey/10 text-honey border border-honey/25 hover:bg-honey/20 transition-colors cursor-pointer">
                            <x-heroicon-o-arrow-uturn-left class="w-3.5 h-3.5" />
                            Reopen Ticket
                        </button>
                    @endif
                </div>

                @if ($record->resolved_at)
                    <div class="mt-4 pt-4 border-t border-honey/8 text-[0.75rem] text-cinnamon">
                        Resolved {{ $record->resolved_at->diffForHumans() }}
                    </div>
                @endif
            </x-central.card>

            {{-- Customer Card --}}
            @if ($record->tenant)
                <x-central.card>
                    <x-central.eyebrow class="mb-3">Customer</x-central.eyebrow>

                    <div class="flex items-start gap-3 mb-4">
                        <div class="shrink-0 w-10 h-10 rounded-full bg-honey/15 border border-honey/25 flex items-center justify-center text-honey font-bold text-[0.85rem]">
                            {{ $initials($record->tenant->name) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-white font-semibold text-[0.9rem] truncate">{{ $record->tenant->name }}</div>
                            <div class="text-cinnamon text-[0.75rem] truncate">{{ $record->tenant->email ?? '—' }}</div>
                        </div>
                    </div>

                    <dl class="space-y-2 text-[0.8rem]">
                        <div class="flex items-center justify-between">
                            <dt class="text-cinnamon">Plan</dt>
                            <dd class="text-white font-semibold capitalize">{{ $record->tenant->plan?->value ?? $record->tenant->plan ?? '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-cinnamon">Tenant ID</dt>
                            <dd class="text-parchment font-mono text-[0.7rem]">{{ $record->tenant->id }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-cinnamon">Signed up</dt>
                            <dd class="text-parchment">{{ $record->tenant->created_at?->format('M j, Y') ?? '—' }}</dd>
                        </div>
                        @if ($record->tenant->trial_ends_at)
                            <div class="flex items-center justify-between">
                                <dt class="text-cinnamon">Trial ends</dt>
                                <dd class="text-parchment">{{ $record->tenant->trial_ends_at->format('M j, Y') }}</dd>
                            </div>
                        @endif
                    </dl>

                    <a href="{{ \App\Filament\Central\Resources\TenantResource::getUrl('view', ['record' => $record->tenant->id]) }}"
                        class="mt-4 w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-[0.8rem] font-semibold bg-espresso text-honey border border-honey/20 hover:border-honey transition-colors no-underline">
                        <x-heroicon-o-arrow-top-right-on-square class="w-3.5 h-3.5" />
                        View Tenant
                    </a>
                </x-central.card>
            @endif

            {{-- Internal Notes --}}
            <x-central.card>
                <div class="flex items-center justify-between mb-3">
                    <x-central.eyebrow>Internal Notes</x-central.eyebrow>
                    <span class="inline-flex items-center gap-1 text-[0.65rem] uppercase tracking-[0.1em] font-bold text-amber-400 bg-amber-500/10 border border-amber-500/25 rounded px-1.5 py-0.5">
                        <x-heroicon-o-lock-closed class="w-2.5 h-2.5" />
                        Staff only
                    </span>
                </div>
                <x-central.textarea wire:model="adminNotesDraft" rows="5" placeholder="Notes only visible to your team…" class="text-[0.85rem]" />
                <div class="mt-2 flex items-center justify-end">
                    <button type="button" wire:click="saveAdminNotes"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[0.75rem] font-semibold bg-honey/10 text-honey border border-honey/25 hover:bg-honey/20 transition-colors cursor-pointer">
                        <x-heroicon-o-bookmark class="w-3 h-3" />
                        Save Notes
                    </button>
                </div>
            </x-central.card>
        </div>
    </div>
</x-filament-panels::page>
