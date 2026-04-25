@php
    $initials = function (string $name): string {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        return strtoupper(substr($parts[0] ?? '?', 0, 1) . substr($parts[1] ?? '', 0, 1));
    };

    $thread = $this->getThread();
    $totalMessages = 1 + $thread->count();
    $tenantDisplayName = $record->tenant?->store_name ?: $record->tenant?->name ?: $record->tenant_id;
    $lastActivity = $thread->isNotEmpty()
        ? $thread->last()->created_at
        : $record->created_at;
@endphp

<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-[1fr_300px] gap-6">
        {{-- ============== MAIN COLUMN ============== --}}
        <div class="space-y-8">
            {{-- Meta strip --}}
            <x-central.card padding="px-5 py-4">
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="shrink-0 w-10 h-10 rounded-xl bg-honey/15 border border-honey/25 flex items-center justify-center text-honey font-bold text-[0.85rem]">
                        {{ $initials($tenantDisplayName) }}
                    </div>
                    <div class="flex items-center gap-2 text-[0.8rem] text-cinnamon">
                        <span class="text-white font-semibold">{{ $tenantDisplayName }}</span>
                        <span class="text-cinnamon/50">•</span>
                        <span>Thread #{{ $record->id }}</span>
                        <span class="text-cinnamon/50">•</span>
                        <span>Last activity {{ $lastActivity->diffForHumans() }}</span>
                    </div>
                </div>
            </x-central.card>

            {{-- Conversation --}}
            <div class="space-y-6">
                <div class="flex items-center justify-between mb-4">
                    <x-central.eyebrow>Conversation</x-central.eyebrow>
                    <span class="text-cinnamon text-[0.75rem]">{{ $totalMessages }} {{ \Illuminate\Support\Str::plural('message', $totalMessages) }}</span>
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
                        'bg-honey/15 border-honey/30 text-honey' => $originalIsAdmin,
                        'bg-cinnamon/20 border-cinnamon/25 text-parchment' => ! $originalIsAdmin,
                    ])>
                        {{ $initials($originalName) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-2 flex-wrap">
                            <span class="text-white font-semibold text-[0.85rem]">{{ $originalName }}</span>
                            <span @class([
                                'text-[0.6rem] uppercase tracking-[0.1em] font-bold px-1.5 py-0.5 rounded',
                                'bg-honey text-warm-black' => $originalIsAdmin,
                                'bg-espresso text-cinnamon' => ! $originalIsAdmin,
                            ])>
                                {{ $originalIsAdmin ? 'Staff' : 'Baker' }}
                            </span>
                            <span class="text-cinnamon text-[0.75rem]">{{ $record->created_at->format('M j, Y · g:i A') }}</span>
                        </div>
                        <div @class([
                            'rounded-xl border p-5',
                            'border-honey/25 bg-honey/5' => $originalIsAdmin,
                            'border-honey/12 bg-warm-black' => ! $originalIsAdmin,
                        ])>
                            <div class="text-parchment text-[0.9rem] leading-relaxed whitespace-pre-wrap">{{ $record->body }}</div>
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
                            'bg-honey/15 border-honey/30 text-honey' => $isAdmin,
                            'bg-cinnamon/20 border-cinnamon/25 text-parchment' => ! $isAdmin,
                        ])>
                            {{ $initials($replyName) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-2 flex-wrap">
                                <span class="text-white font-semibold text-[0.85rem]">{{ $replyName }}</span>
                                <span @class([
                                    'text-[0.6rem] uppercase tracking-[0.1em] font-bold px-1.5 py-0.5 rounded',
                                    'bg-honey text-warm-black' => $isAdmin,
                                    'bg-espresso text-cinnamon' => ! $isAdmin,
                                ])>
                                    {{ $isAdmin ? 'Staff' : 'Baker' }}
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

            {{-- Reply composer --}}
            <x-central.card>
                <div class="flex items-center justify-between mb-3">
                    <x-central.eyebrow>Reply to baker</x-central.eyebrow>
                    <span class="inline-flex items-center gap-1.5 text-[0.7rem] text-cinnamon">
                        <x-heroicon-o-envelope class="w-3.5 h-3.5" />
                        Visible to {{ $tenantDisplayName }}
                    </span>
                </div>
                <form wire:submit="sendReply">
                    <x-central.textarea wire:model="replyBody" rows="4" placeholder="Type your reply…" />
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
        </div>

        {{-- ============== SIDEBAR ============== --}}
        <div class="space-y-6">
            {{-- Baker Card --}}
            @if ($record->tenant)
                <x-central.card>
                    <x-central.eyebrow class="mb-3">Baker</x-central.eyebrow>

                    <div class="flex items-start gap-3 mb-4">
                        <div class="shrink-0 w-10 h-10 rounded-full bg-honey/15 border border-honey/25 flex items-center justify-center text-honey font-bold text-[0.85rem]">
                            {{ $initials($tenantDisplayName) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-white font-semibold text-[0.9rem] truncate">{{ $tenantDisplayName }}</div>
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
                    </dl>

                    <a href="{{ \App\Filament\Central\Resources\TenantResource::getUrl('view', ['record' => $record->tenant->id]) }}"
                        class="mt-4 w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-[0.8rem] font-semibold bg-espresso text-honey border border-honey/20 hover:border-honey transition-colors no-underline">
                        <x-heroicon-o-arrow-top-right-on-square class="w-3.5 h-3.5" />
                        View Tenant
                    </a>
                </x-central.card>
            @endif

            {{-- Thread Summary --}}
            <x-central.card>
                <x-central.eyebrow class="mb-3">Thread</x-central.eyebrow>
                <dl class="space-y-2 text-[0.8rem]">
                    <div class="flex items-center justify-between">
                        <dt class="text-cinnamon">Messages</dt>
                        <dd class="text-white font-semibold">{{ $totalMessages }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-cinnamon">Started</dt>
                        <dd class="text-parchment">{{ $record->created_at->format('M j, Y') }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-cinnamon">Last activity</dt>
                        <dd class="text-parchment">{{ $lastActivity->diffForHumans() }}</dd>
                    </div>
                </dl>
            </x-central.card>
        </div>
    </div>
</x-filament-panels::page>
