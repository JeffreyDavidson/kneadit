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
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_340px]">
        {{-- ============== MAIN COLUMN ============== --}}
        <div class="space-y-8">
            {{-- Ticket Meta Strip (subject is handled by Filament's page heading) --}}
            <x-central.card padding="px-5 py-4">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="bg-honey/15 border-honey/25 text-honey flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border text-[0.85rem] font-bold">
                        {{ $initials($record->tenant?->name ?? 'T') }}
                    </div>
                    <div class="text-cinnamon flex items-center gap-2 text-[0.8rem]">
                        <span class="font-semibold text-white">{{ $record->tenant?->name ?? $record->tenant_id }}</span>
                        <span class="text-cinnamon/50">•</span>
                        <span>Ticket #{{ $record->id }}</span>
                        <span class="text-cinnamon/50">•</span>
                        <span>{{ $record->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </x-central.card>

            {{-- Conversation --}}
            <div class="space-y-6">
                <div class="mb-1 flex items-center justify-between">
                    <x-central.eyebrow>Conversation</x-central.eyebrow>
                    <span class="text-cinnamon text-[0.75rem]">{{ $record->replies->count() + 1 }} {{ \Illuminate\Support\Str::plural('message', $record->replies->count() + 1) }}</span>
                </div>

                {{-- Original Message (always from tenant) --}}
                <div class="flex gap-3">
                    <div class="bg-cinnamon/20 border-cinnamon/25 text-parchment flex h-9 w-9 shrink-0 items-center justify-center rounded-full border text-[0.75rem] font-bold">
                        {{ $initials($record->tenant?->name ?? 'T') }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span class="text-[0.85rem] font-semibold text-white">{{ $record->tenant?->name ?? 'Customer' }}</span>
                            <span class="text-cinnamon bg-espresso rounded px-1.5 py-0.5 text-[0.6rem] font-bold tracking-[0.1em] uppercase">Customer</span>
                            <span class="text-cinnamon text-[0.75rem]">{{ $record->created_at->format('M j, Y · g:i A') }}</span>
                        </div>
                        <div class="border-honey/12 bg-warm-black rounded-xl border p-5">
                            <div class="text-parchment text-[0.9rem] leading-relaxed whitespace-pre-wrap">
                                {{ $record->body }}
                            </div>
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
                        <div class="min-w-0 flex-1">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <span class="text-[0.85rem] font-semibold text-white">{{ $reply->author_name }}</span>
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
                                <div class="text-parchment text-[0.9rem] leading-relaxed whitespace-pre-wrap">
                                    {{ $reply->body }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Reply Composer --}}
            @if ($statusValue !== SupportTicketStatus::Closed->value)
                <x-central.card>
                    <div class="mb-3 flex items-center justify-between">
                        <x-central.eyebrow>Reply to customer</x-central.eyebrow>
                        <span class="text-cinnamon inline-flex items-center gap-1.5 text-[0.7rem]">
                            <x-heroicon-o-envelope class="h-3.5 w-3.5" />
                            Visible to {{ $record->tenant?->name ?? 'customer' }}
                        </span>
                    </div>
                    <form wire:submit="addReply">
                        <x-central.textarea wire:model="replyBody" rows="4" placeholder="Write your reply…" />
                        @error('replyBody')
                            <p class="mt-1.5 text-[0.8rem] text-red-500">{{ $message }}</p>
                        @enderror
                        <div class="mt-3 flex items-center justify-end gap-2">
                            <x-central.button type="submit" class="gap-1.5">
                                <x-heroicon-o-paper-airplane class="h-3.5 w-3.5" stroke-width="2.5" />
                                Send Reply
                            </x-central.button>
                        </div>
                    </form>
                </x-central.card>
            @else
                <div class="text-cinnamon inline-flex items-center gap-2 text-[0.8rem]">
                    <x-heroicon-o-lock-closed class="h-4 w-4" />
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

                <div class="mb-4 text-[0.8rem]">
                    <span class="text-cinnamon">Priority:</span>
                    <span class="{{ $priorityTone }} font-semibold capitalize">{{ $priorityValue }}</span>
                </div>

                <div class="space-y-2">
                    @if ($statusValue === 'open')
                        <button
                            type="button"
                            wire:click="updateStatus('in_progress')"
                            class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-lg border border-amber-500/25 bg-amber-500/10 px-3 py-2 text-[0.8rem] font-semibold text-amber-400 transition-colors hover:bg-amber-500/20"
                        >
                            <x-heroicon-o-bolt class="h-3.5 w-3.5" />
                            Mark In Progress
                        </button>
                    @endif

                    @if (in_array($statusValue, ['open', 'in_progress'], true))
                        <button
                            type="button"
                            wire:click="updateStatus('resolved')"
                            class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-lg border border-emerald-500/25 bg-emerald-500/10 px-3 py-2 text-[0.8rem] font-semibold text-emerald-400 transition-colors hover:bg-emerald-500/20"
                        >
                            <x-heroicon-o-check-circle class="h-3.5 w-3.5" />
                            Mark Resolved
                        </button>
                    @endif

                    @if ($statusValue !== 'closed')
                        <button
                            type="button"
                            wire:click="updateStatus('closed')"
                            class="bg-cinnamon/10 text-cinnamon border-cinnamon/25 hover:bg-cinnamon/20 inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-lg border px-3 py-2 text-[0.8rem] font-semibold transition-colors"
                        >
                            <x-heroicon-o-archive-box class="h-3.5 w-3.5" />
                            Close Ticket
                        </button>
                    @else
                        <button
                            type="button"
                            wire:click="updateStatus('open')"
                            class="bg-honey/10 text-honey border-honey/25 hover:bg-honey/20 inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-lg border px-3 py-2 text-[0.8rem] font-semibold transition-colors"
                        >
                            <x-heroicon-o-arrow-uturn-left class="h-3.5 w-3.5" />
                            Reopen Ticket
                        </button>
                    @endif
                </div>

                @if ($record->resolved_at)
                    <div class="border-honey/8 text-cinnamon mt-4 border-t pt-4 text-[0.75rem]">
                        Resolved {{ $record->resolved_at->diffForHumans() }}
                    </div>
                @endif
            </x-central.card>

            {{-- Customer Card --}}
            @if ($record->tenant)
                <x-central.card>
                    <x-central.eyebrow class="mb-3">Customer</x-central.eyebrow>

                    <div class="mb-4 flex items-start gap-3">
                        <div class="bg-honey/15 border-honey/25 text-honey flex h-10 w-10 shrink-0 items-center justify-center rounded-full border text-[0.85rem] font-bold">
                            {{ $initials($record->tenant->name) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-[0.9rem] font-semibold text-white">
                                {{ $record->tenant->name }}
                            </div>
                            <div class="text-cinnamon truncate text-[0.75rem]">{{ $record->tenant->email ?? '—' }}</div>
                        </div>
                    </div>

                    <dl class="space-y-2 text-[0.8rem]">
                        <div class="flex items-center justify-between">
                            <dt class="text-cinnamon">Plan</dt>
                            <dd class="font-semibold text-white capitalize">
                                {{ $record->tenant->plan?->value ?? $record->tenant->plan ?? '—' }}
                            </dd>
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

                    <a
                        href="{{ \App\Filament\Central\Resources\TenantResource::getUrl('view', ['record' => $record->tenant->id]) }}"
                        class="bg-espresso text-honey border-honey/20 hover:border-honey mt-4 inline-flex w-full items-center justify-center gap-1.5 rounded-lg border px-3 py-2 text-[0.8rem] font-semibold no-underline transition-colors"
                    >
                        <x-heroicon-o-arrow-top-right-on-square class="h-3.5 w-3.5" />
                        View Tenant
                    </a>
                </x-central.card>
            @endif

            {{-- Internal Notes --}}
            <x-central.card>
                <div class="mb-3 flex items-center justify-between">
                    <x-central.eyebrow>Internal Notes</x-central.eyebrow>
                    <span class="inline-flex items-center gap-1 rounded border border-amber-500/25 bg-amber-500/10 px-1.5 py-0.5 text-[0.65rem] font-bold tracking-[0.1em] text-amber-400 uppercase">
                        <x-heroicon-o-lock-closed class="h-2.5 w-2.5" />
                        Staff only
                    </span>
                </div>
                <x-central.textarea
                    wire:model="adminNotesDraft"
                    rows="5"
                    placeholder="Notes only visible to your team…"
                    class="text-[0.85rem]"
                />
                <div class="mt-2 flex items-center justify-end">
                    <button
                        type="button"
                        wire:click="saveAdminNotes"
                        class="bg-honey/10 text-honey border-honey/25 hover:bg-honey/20 inline-flex cursor-pointer items-center gap-1.5 rounded-lg border px-3 py-1.5 text-[0.75rem] font-semibold transition-colors"
                    >
                        <x-heroicon-o-bookmark class="h-3 w-3" />
                        Save Notes
                    </button>
                </div>
            </x-central.card>
        </div>
    </div>
</x-filament-panels::page>
