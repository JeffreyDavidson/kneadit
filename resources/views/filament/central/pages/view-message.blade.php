@php
    $initials = function (string $name): string {
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        return strtoupper(substr($parts[0] ?? '?', 0, 1).substr($parts[1] ?? '', 0, 1));
    };

    $thread = $this->getThread();
    $totalMessages = 1 + $thread->count();
    $tenantDisplayName = $record->tenant?->store_name ?: $record->tenant?->name ?: $record->tenant_id;
    $lastActivity = $thread->isNotEmpty()
        ? $thread->last()->created_at
        : $record->created_at;
@endphp

<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-[1fr_300px]">
        {{-- ============== MAIN COLUMN ============== --}}
        <div class="space-y-8">
            {{-- Meta strip --}}
            <x-central.card padding="px-5 py-4">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-(--kn-honey)/25 bg-(--kn-warning-tint) text-[0.85rem] font-bold text-(--kn-honey-text)">
                        {{ $initials($tenantDisplayName) }}
                    </div>
                    <div class="flex items-center gap-2 text-[0.8rem] text-(--kn-muted)">
                        <span class="font-semibold text-(--kn-ink)">{{ $tenantDisplayName }}</span>
                        <span class="text-(--kn-muted)">•</span>
                        <span>Thread #{{ $record->id }}</span>
                        <span class="text-(--kn-muted)">•</span>
                        <span>Last activity {{ $lastActivity->diffForHumans() }}</span>
                    </div>
                </div>
            </x-central.card>

            {{-- Conversation --}}
            <div class="space-y-6">
                <div class="mb-4 flex items-center justify-between">
                    <x-central.eyebrow>Conversation</x-central.eyebrow>
                    <span class="text-[0.75rem] text-(--kn-muted)">{{ $totalMessages }} {{ \Illuminate\Support\Str::plural('message', $totalMessages) }}</span>
                </div>

                {{-- Original message --}}
                @php
                    $originalType = $record->sender_type instanceof \BackedEnum ? $record->sender_type->value : $record->sender_type;
                    $originalIsAdmin = $originalType === 'admin';
                    $originalName = $originalIsAdmin ? 'KneadIt Team' : $tenantDisplayName;
                @endphp
                <div class="flex gap-3">
                    <div @class([
                        'shrink-0 w-9 h-9 rounded-full flex items-center justify-center font-bold text-[0.75rem] border',
                        'bg-(--kn-warning-tint) border-(--kn-honey)/30 text-(--kn-honey-text)' => $originalIsAdmin,
                        'bg-(--kn-surface-hover) border-(--kn-border) text-(--kn-ink)' => ! $originalIsAdmin,
                    ])>
                        {{ $initials($originalName) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span class="text-[0.85rem] font-semibold text-(--kn-ink)">{{ $originalName }}</span>
                            <span @class([
                                'text-[0.6rem] uppercase tracking-[0.1em] font-bold px-1.5 py-0.5 rounded',
                                'bg-(--kn-honey) text-(--kn-on-honey)' => $originalIsAdmin,
                                'bg-(--kn-surface-sunken) text-(--kn-muted)' => ! $originalIsAdmin,
                            ])>
                                {{ $originalIsAdmin ? 'Staff' : 'Baker' }}
                            </span>
                            <span class="text-[0.75rem] text-(--kn-muted)">{{ $record->created_at->format('M j, Y · g:i A') }}</span>
                        </div>
                        <div @class([
                            'rounded-xl border p-5',
                            'border-(--kn-honey)/25 bg-(--kn-surface-hover)' => $originalIsAdmin,
                            'border-(--kn-border) bg-(--kn-surface)' => ! $originalIsAdmin,
                        ])>
                            <div class="text-[0.9rem] leading-relaxed whitespace-pre-wrap text-(--kn-ink)">
                                {{ $record->body }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Thread replies --}}
                @foreach ($thread as $reply)
                    @php
                        $replyType = $reply->sender_type instanceof \BackedEnum ? $reply->sender_type->value : $reply->sender_type;
                        $isAdmin = $replyType === 'admin';
                        $replyName = $isAdmin ? 'KneadIt Team' : $tenantDisplayName;
                    @endphp
                    <div class="flex gap-3">
                        <div @class([
                            'shrink-0 w-9 h-9 rounded-full flex items-center justify-center font-bold text-[0.75rem] border',
                            'bg-(--kn-warning-tint) border-(--kn-honey)/30 text-(--kn-honey-text)' => $isAdmin,
                            'bg-(--kn-surface-hover) border-(--kn-border) text-(--kn-ink)' => ! $isAdmin,
                        ])>
                            {{ $initials($replyName) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <span class="text-[0.85rem] font-semibold text-(--kn-ink)">{{ $replyName }}</span>
                                <span @class([
                                    'text-[0.6rem] uppercase tracking-[0.1em] font-bold px-1.5 py-0.5 rounded',
                                    'bg-(--kn-honey) text-(--kn-on-honey)' => $isAdmin,
                                    'bg-(--kn-surface-sunken) text-(--kn-muted)' => ! $isAdmin,
                                ])>
                                    {{ $isAdmin ? 'Staff' : 'Baker' }}
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

            {{-- Reply composer --}}
            <x-central.card>
                <div class="mb-3 flex items-center justify-between">
                    <x-central.eyebrow>Reply to baker</x-central.eyebrow>
                    <span class="inline-flex items-center gap-1.5 text-[0.7rem] text-(--kn-muted)">
                        <x-heroicon-o-envelope class="h-3.5 w-3.5" />
                        Visible to {{ $tenantDisplayName }}
                    </span>
                </div>
                <form wire:submit="sendReply">
                    <x-central.textarea wire:model="replyBody" rows="4" placeholder="Type your reply…" />
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
        </div>

        {{-- ============== SIDEBAR ============== --}}
        <div class="space-y-6">
            {{-- Baker Card --}}
            @if ($record->tenant)
                <x-central.card>
                    <x-central.eyebrow class="mb-3">Baker</x-central.eyebrow>

                    <div class="mb-4 flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-(--kn-honey)/25 bg-(--kn-warning-tint) text-[0.85rem] font-bold text-(--kn-honey-text)">
                            {{ $initials($tenantDisplayName) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-[0.9rem] font-semibold text-(--kn-ink)">
                                {{ $tenantDisplayName }}
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

            {{-- Thread Summary --}}
            <x-central.card>
                <x-central.eyebrow class="mb-3">Thread</x-central.eyebrow>
                <dl class="space-y-2 text-[0.8rem]">
                    <div class="flex items-center justify-between">
                        <dt class="text-(--kn-muted)">Messages</dt>
                        <dd class="font-semibold text-(--kn-ink)">{{ $totalMessages }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-(--kn-muted)">Started</dt>
                        <dd class="text-(--kn-ink)">{{ $record->created_at->format('M j, Y') }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-(--kn-muted)">Last activity</dt>
                        <dd class="text-(--kn-ink)">{{ $lastActivity->diffForHumans() }}</dd>
                    </div>
                </dl>
            </x-central.card>
        </div>
    </div>
</x-filament-panels::page>
