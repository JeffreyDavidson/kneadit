@php
    use App\Enums\Platform\SupportReplyAuthorType;
    use App\Enums\Platform\SupportTicketStatus;

    $statusValue = $record->status instanceof \BackedEnum ? $record->status->value : $record->status;
    $priorityValue = $record->priority instanceof \BackedEnum ? $record->priority->value : $record->priority;

    $statusTone = match ($statusValue) {
        'open' => ['bg' => 'bg-(--kn-danger-tint)', 'border' => 'border-(--kn-danger)/25', 'text' => 'text-(--kn-danger)', 'dot' => 'bg-(--kn-danger)'],
        'in_progress' => ['bg' => 'bg-(--kn-warning-tint)', 'border' => 'border-(--kn-warning)/25', 'text' => 'text-(--kn-warning)', 'dot' => 'bg-(--kn-warning)'],
        'resolved' => ['bg' => 'bg-(--kn-success-tint)', 'border' => 'border-(--kn-success)/25', 'text' => 'text-(--kn-success)', 'dot' => 'bg-(--kn-success)'],
        'closed' => ['bg' => 'bg-(--kn-surface-hover)', 'border' => 'border-(--kn-border)', 'text' => 'text-(--kn-muted)', 'dot' => 'bg-(--kn-muted)'],
        default => ['bg' => 'bg-(--kn-surface-hover)', 'border' => 'border-(--kn-border)', 'text' => 'text-(--kn-muted)', 'dot' => 'bg-(--kn-muted)'],
    };

    $priorityTone = match ($priorityValue) {
        'high' => 'text-(--kn-danger)',
        'normal' => 'text-(--kn-info)',
        'low' => 'text-(--kn-muted)',
        default => 'text-(--kn-muted)',
    };

    $initials = function (string $name): string {
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        return strtoupper(substr($parts[0] ?? '?', 0, 1).substr($parts[1] ?? '', 0, 1));
    };
@endphp

<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_340px]">
        {{-- ============== MAIN COLUMN ============== --}}
        <div class="space-y-8">
            {{-- Ticket Meta Strip (subject is handled by Filament's page heading) --}}
            <x-central.card padding="px-5 py-4">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-(--kn-honey)/25 bg-(--kn-warning-tint) text-[0.85rem] font-bold text-(--kn-honey-text)">
                        {{ $initials($record->tenant?->name ?? 'T') }}
                    </div>
                    <div class="flex items-center gap-2 text-[0.8rem] text-(--kn-muted)">
                        <span class="font-semibold text-(--kn-ink)">{{ $record->tenant?->name ?? $record->tenant_id }}</span>
                        <span class="text-(--kn-muted)">•</span>
                        <span>Ticket #{{ $record->id }}</span>
                        <span class="text-(--kn-muted)">•</span>
                        <span>{{ $record->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </x-central.card>

            {{-- Conversation --}}
            <div class="space-y-6">
                <div class="mb-1 flex items-center justify-between">
                    <x-central.eyebrow>Conversation</x-central.eyebrow>
                    <span class="text-[0.75rem] text-(--kn-muted)">{{ $record->replies->count() + 1 }} {{ \Illuminate\Support\Str::plural('message', $record->replies->count() + 1) }}</span>
                </div>

                {{-- Original Message (always from tenant) --}}
                <div class="flex gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-(--kn-border) bg-(--kn-surface-hover) text-[0.75rem] font-bold text-(--kn-ink)">
                        {{ $initials($record->tenant?->name ?? 'T') }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span class="text-[0.85rem] font-semibold text-(--kn-ink)">{{ $record->tenant?->name ?? 'Customer' }}</span>
                            <span class="rounded bg-(--kn-surface-sunken) px-1.5 py-0.5 text-[0.6rem] font-bold tracking-[0.1em] text-(--kn-muted) uppercase">Customer</span>
                            <span class="text-[0.75rem] text-(--kn-muted)">{{ $record->created_at->format('M j, Y · g:i A') }}</span>
                        </div>
                        <div class="rounded-xl border border-(--kn-border) bg-(--kn-surface) p-5">
                            <div class="text-[0.9rem] leading-relaxed whitespace-pre-wrap text-(--kn-ink)">
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
                            'bg-(--kn-warning-tint) border-(--kn-honey)/30 text-(--kn-honey-text)' => $isAdmin,
                            'bg-(--kn-surface-hover) border-(--kn-border) text-(--kn-ink)' => ! $isAdmin,
                        ])>
                            {{ $initials($reply->author_name) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <span class="text-[0.85rem] font-semibold text-(--kn-ink)">{{ $reply->author_name }}</span>
                                <span @class([
                                    'text-[0.6rem] uppercase tracking-[0.1em] font-bold px-1.5 py-0.5 rounded',
                                    'bg-(--kn-honey) text-(--kn-on-honey)' => $isAdmin,
                                    'bg-(--kn-surface-sunken) text-(--kn-muted)' => ! $isAdmin,
                                ])>
                                    {{ $isAdmin ? 'Staff' : 'Customer' }}
                                </span>
                                <span class="text-[0.75rem] text-(--kn-muted)">{{ $reply->created_at->format('M j, Y · g:i A') }}</span>
                            </div>
                            <div @class([
                                'rounded-xl border p-5',
                                'border-(--kn-honey)/25 bg-(--kn-surface-hover)' => $isAdmin,
                                'border-(--kn-border) bg-(--kn-surface)' => ! $isAdmin,
                            ])>
                                <div class="text-[0.9rem] leading-relaxed whitespace-pre-wrap text-(--kn-ink)">
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
                        <span class="inline-flex items-center gap-1.5 text-[0.7rem] text-(--kn-muted)">
                            <x-heroicon-o-envelope class="h-3.5 w-3.5" />
                            Visible to {{ $record->tenant?->name ?? 'customer' }}
                        </span>
                    </div>
                    <form wire:submit="addReply">
                        <x-central.textarea wire:model="replyBody" rows="4" placeholder="Write your reply…" />
                        @error('replyBody')
                            <p class="mt-1.5 text-[0.8rem] text-(--kn-danger)">{{ $message }}</p>
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
                <div class="inline-flex items-center gap-2 text-[0.8rem] text-(--kn-muted)">
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
                    <span class="text-(--kn-muted)">Priority:</span>
                    <span class="{{ $priorityTone }} font-semibold capitalize">{{ $priorityValue }}</span>
                </div>

                <div class="space-y-2">
                    @if ($statusValue === 'open')
                        <button
                            type="button"
                            wire:click="updateStatus('in_progress')"
                            class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-lg border border-(--kn-warning)/25 bg-(--kn-warning-tint) px-3 py-2 text-[0.8rem] font-semibold text-(--kn-warning) transition-colors hover:bg-(--kn-warning)/20"
                        >
                            <x-heroicon-o-bolt class="h-3.5 w-3.5" />
                            Mark In Progress
                        </button>
                    @endif

                    @if (in_array($statusValue, ['open', 'in_progress'], true))
                        <button
                            type="button"
                            wire:click="updateStatus('resolved')"
                            class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-lg border border-(--kn-success)/25 bg-(--kn-success-tint) px-3 py-2 text-[0.8rem] font-semibold text-(--kn-success) transition-colors hover:bg-(--kn-success)/20"
                        >
                            <x-heroicon-o-check-circle class="h-3.5 w-3.5" />
                            Mark Resolved
                        </button>
                    @endif

                    @if ($statusValue !== 'closed')
                        <button
                            type="button"
                            wire:click="updateStatus('closed')"
                            class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-lg border border-(--kn-border) bg-(--kn-surface-hover) px-3 py-2 text-[0.8rem] font-semibold text-(--kn-muted) transition-colors hover:bg-(--kn-surface-hover)"
                        >
                            <x-heroicon-o-archive-box class="h-3.5 w-3.5" />
                            Close Ticket
                        </button>
                    @else
                        <button
                            type="button"
                            wire:click="updateStatus('open')"
                            class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-lg border border-(--kn-honey)/25 bg-(--kn-warning-tint) px-3 py-2 text-[0.8rem] font-semibold text-(--kn-honey-text) transition-colors hover:bg-(--kn-warning-tint)"
                        >
                            <x-heroicon-o-arrow-uturn-left class="h-3.5 w-3.5" />
                            Reopen Ticket
                        </button>
                    @endif
                </div>

                @if ($record->resolved_at)
                    <div class="mt-4 border-t border-(--kn-border) pt-4 text-[0.75rem] text-(--kn-muted)">
                        Resolved {{ $record->resolved_at->diffForHumans() }}
                    </div>
                @endif
            </x-central.card>

            {{-- Customer Card --}}
            @if ($record->tenant)
                <x-central.card>
                    <x-central.eyebrow class="mb-3">Customer</x-central.eyebrow>

                    <div class="mb-4 flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-(--kn-honey)/25 bg-(--kn-warning-tint) text-[0.85rem] font-bold text-(--kn-honey-text)">
                            {{ $initials($record->tenant->name) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-[0.9rem] font-semibold text-(--kn-ink)">
                                {{ $record->tenant->name }}
                            </div>
                            <div class="truncate text-[0.75rem] text-(--kn-muted)">
                                {{ $record->tenant->email ?? '—' }}
                            </div>
                        </div>
                    </div>

                    <dl class="space-y-2 text-[0.8rem]">
                        <div class="flex items-center justify-between">
                            <dt class="text-(--kn-muted)">Plan</dt>
                            <dd class="font-semibold text-(--kn-ink) capitalize">
                                {{ $record->tenant->plan?->value ?? $record->tenant->plan ?? '—' }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-(--kn-muted)">Tenant ID</dt>
                            <dd class="font-mono text-[0.7rem] text-(--kn-ink)">{{ $record->tenant->id }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-(--kn-muted)">Signed up</dt>
                            <dd class="text-(--kn-ink)">{{ $record->tenant->created_at?->format('M j, Y') ?? '—' }}</dd>
                        </div>
                        @if ($record->tenant->trial_ends_at)
                            <div class="flex items-center justify-between">
                                <dt class="text-(--kn-muted)">Trial ends</dt>
                                <dd class="text-(--kn-ink)">{{ $record->tenant->trial_ends_at->format('M j, Y') }}</dd>
                            </div>
                        @endif
                    </dl>

                    <a
                        href="{{ \App\Filament\Central\Resources\TenantResource::getUrl('view', ['record' => $record->tenant->id]) }}"
                        class="mt-4 inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-(--kn-border) bg-(--kn-surface-sunken) px-3 py-2 text-[0.8rem] font-semibold text-(--kn-honey-text) no-underline transition-colors hover:border-(--kn-honey)"
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
                    <span class="inline-flex items-center gap-1 rounded border border-(--kn-warning)/25 bg-(--kn-warning-tint) px-1.5 py-0.5 text-[0.65rem] font-bold tracking-[0.1em] text-(--kn-warning) uppercase">
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
                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-(--kn-honey)/25 bg-(--kn-warning-tint) px-3 py-1.5 text-[0.75rem] font-semibold text-(--kn-honey-text) transition-colors hover:bg-(--kn-warning-tint)"
                    >
                        <x-heroicon-o-bookmark class="h-3 w-3" />
                        Save Notes
                    </button>
                </div>
            </x-central.card>
        </div>
    </div>
</x-filament-panels::page>
