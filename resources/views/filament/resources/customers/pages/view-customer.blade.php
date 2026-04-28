@php
    use Carbon\Carbon;

    /** @var array<string, mixed> $detail */
    $stats = $detail['stats'];
    /** @var array<int, array<string, mixed>> $orders */
    $orders = $detail['orders'];

    $customer = $record;
    $isAtRisk = $stats['is_at_risk'] ?? false;
    $createdAt = $stats['created_at'] ?? null;

    $initials = function (string $name): string {
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        return strtoupper(substr($parts[0] ?? '?', 0, 1) . substr($parts[1] ?? '', 0, 1));
    };

    $row = function (string $label, ?string $value, bool $mono = false) {
        return ['label' => $label, 'value' => $value, 'mono' => $mono];
    };

    $contactRows = [
        $row('Email', $detail['email'] ?: '—'),
        $row('Phone', $detail['phone'] ?: '—'),
        $row('Address', $detail['address'] ?: '—'),
    ];

    $accountRows = [
        $row('Customer Since', $createdAt?->format('M j, Y') ?? '—'),
        $row('Last Order', $stats['last_order'] ?? 'Never'),
        $row('Days Since Last Order', $stats['days_since_last_order'] !== null ? $stats['days_since_last_order'] . ' days' : '—'),
    ];
@endphp

<x-filament-panels::page>
    {{-- ============== HERO STRIP ============== --}}
    <div class="mb-6 bg-brand-900 border border-brand-800/60 rounded-xl p-6 flex flex-col md:flex-row md:items-center gap-5">
        <div class="flex items-center gap-4 flex-1 min-w-0">
            <div class="shrink-0 w-14 h-14 rounded-xl bg-brand-300/15 border border-brand-300/25 flex items-center justify-center text-brand-300 font-bold text-[1.15rem]">
                {{ $initials($customer->name ?: $detail['email'] ?? '?') }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <h2 class="text-white text-[1.35rem] font-bold leading-tight truncate">{{ $customer->name }}</h2>
                </div>
                @if ($detail['email'])
                    <a href="mailto:{{ $detail['email'] }}"
                        class="inline-flex items-center gap-1.5 text-brand-400 text-[0.85rem] hover:text-brand-300 transition-colors">
                        {{ $detail['email'] }}
                    </a>
                @endif
            </div>
        </div>

        {{-- Status pills --}}
        <div class="flex items-center gap-2 flex-wrap">
            @if ($isAtRisk)
                <span class="inline-flex items-center gap-1.5 bg-red-500/15 border border-red-500/25 text-red-400 text-[0.7rem] font-bold uppercase tracking-[0.08em] rounded-full px-2.5 py-1">
                    <x-heroicon-o-exclamation-triangle class="w-3 h-3" />
                    At Risk
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 bg-emerald-500/15 border border-emerald-500/25 text-emerald-400 text-[0.7rem] font-bold uppercase tracking-[0.08em] rounded-full px-2.5 py-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Active
                </span>
            @endif

            @if (($stats['total_points'] ?? 0) > 0)
                <span class="inline-flex items-center gap-1 bg-brand-300/10 border border-brand-300/25 text-brand-300 text-[0.7rem] font-bold uppercase tracking-[0.08em] rounded-full px-2.5 py-1">
                    <x-heroicon-o-sparkles class="w-3 h-3" />
                    {{ number_format($stats['total_points']) }} pts
                </span>
            @endif

            @if ($createdAt)
                <span class="inline-flex items-center gap-1 bg-brand-800 border border-brand-300/15 text-brand-200 text-[0.7rem] font-semibold uppercase tracking-[0.08em] rounded-full px-2.5 py-1">
                    <x-heroicon-o-calendar class="w-3 h-3" />
                    Since {{ $createdAt->format('M Y') }}
                </span>
            @endif
        </div>
    </div>

    {{-- ============== TABS ============== --}}
    <div x-data="{ tab: 'overview' }" class="space-y-6">
        <div class="border-b border-brand-300/12 flex items-center gap-1 overflow-x-auto">
            @php
                $tabs = [
                    'overview' => ['label' => 'Overview', 'icon' => 'chart-bar-square'],
                    'orders' => ['label' => 'Orders', 'icon' => 'receipt-percent', 'count' => count($orders)],
                    'notes' => ['label' => 'Notes', 'icon' => 'pencil-square', 'count' => $customer->customerNotes()->count()],
                ];
            @endphp
            @foreach ($tabs as $key => $t)
                <button type="button" @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}'
                        ? 'text-white border-brand-300'
                        : 'text-brand-400 border-transparent hover:text-brand-200'"
                    class="inline-flex items-center gap-2 px-4 py-2.5 -mb-px border-b-2 text-[0.85rem] font-semibold transition-colors cursor-pointer whitespace-nowrap">
                    @switch($t['icon'])
                        @case('chart-bar-square') <x-heroicon-o-chart-bar-square class="w-4 h-4" /> @break
                        @case('receipt-percent') <x-heroicon-o-receipt-percent class="w-4 h-4" /> @break
                        @case('pencil-square') <x-heroicon-o-pencil-square class="w-4 h-4" /> @break
                    @endswitch
                    {{ $t['label'] }}
                    @isset($t['count'])
                        <span :class="tab === '{{ $key }}' ? 'bg-brand-300/15 text-brand-300' : 'bg-brand-800 text-brand-400'"
                            class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-[0.7rem] font-bold transition-colors">
                            {{ $t['count'] }}
                        </span>
                    @endisset
                </button>
            @endforeach
        </div>

        {{-- ============== TAB: OVERVIEW ============== --}}
        <div x-show="tab === 'overview'" x-cloak class="space-y-6">
            {{-- Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-4">
                    <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-1">Lifetime Value</div>
                    <div class="text-white font-bold text-[1.5rem] leading-none">@money($stats['total_spent'])</div>
                </div>
                <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-4">
                    <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-1">Orders</div>
                    <div class="text-white font-bold text-[1.5rem] leading-none">{{ number_format($stats['total_orders']) }}</div>
                </div>
                <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-4">
                    <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-1">Avg Order</div>
                    <div class="text-white font-bold text-[1.5rem] leading-none">@money($stats['avg_order_value'])</div>
                </div>
                <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-4">
                    <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-1">Points</div>
                    <div class="text-white font-bold text-[1.5rem] leading-none">{{ number_format($stats['total_points'] ?? 0) }}</div>
                </div>
                <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-4">
                    <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-1">Lifetime Points</div>
                    <div class="text-white font-bold text-[1.5rem] leading-none">{{ number_format($stats['lifetime_points'] ?? 0) }}</div>
                </div>
                <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-4">
                    <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-1">Last Order</div>
                    <div class="text-white font-semibold text-[0.95rem] leading-tight mt-0.5">
                        {{ ($stats['last_order_at'] ?? null) ? Carbon::parse($stats['last_order_at'])->diffForHumans() : 'Never' }}
                    </div>
                </div>
            </div>

            {{-- Details --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-6">
                    <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-4">Contact</div>
                    <dl class="divide-y divide-brand-700/40">
                        @foreach ($contactRows as $r)
                            <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                                <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">{{ $r['label'] }}</dt>
                                <dd class="text-white text-[0.85rem] font-semibold text-right truncate {{ $r['mono'] ? 'font-mono text-brand-200' : '' }}">{{ $r['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-6">
                    <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-4">Account</div>
                    <dl class="divide-y divide-brand-700/40">
                        @foreach ($accountRows as $r)
                            <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                                <dt class="text-brand-400 text-[0.8rem] shrink-0 pt-0.5">{{ $r['label'] }}</dt>
                                <dd class="text-white text-[0.85rem] font-semibold text-right truncate {{ $r['mono'] ? 'font-mono text-brand-200' : '' }}">{{ $r['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </div>
        </div>

        {{-- ============== TAB: ORDERS ============== --}}
        <div x-show="tab === 'orders'" x-cloak>
            <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold">Order History</div>
                    <span class="text-brand-400 text-[0.75rem]">{{ count($orders) }} total</span>
                </div>

                @if ($orders === [])
                    <div class="text-center py-12">
                        <x-heroicon-o-receipt-percent class="w-10 h-10 text-brand-400/40 mx-auto mb-3" />
                        <div class="text-brand-200 text-[0.9rem] font-semibold">No orders yet</div>
                        <div class="text-brand-400 text-[0.8rem] mt-1">Orders placed by this customer will appear here.</div>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-[0.85rem]">
                            <thead class="border-b border-brand-700/40 text-[0.7rem] uppercase tracking-[0.05em] text-brand-400">
                                <tr>
                                    <th class="py-2 pr-4 font-semibold">Order</th>
                                    <th class="py-2 pr-4 font-semibold">Date</th>
                                    <th class="py-2 pr-4 font-semibold">Status</th>
                                    <th class="py-2 pr-4 font-semibold">Payment</th>
                                    <th class="py-2 pr-4 text-right font-semibold">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-brand-700/40">
                                @foreach ($orders as $order)
                                    <tr>
                                        <td class="py-2.5 pr-4 font-mono text-brand-200">{{ $order['order_number'] }}</td>
                                        <td class="py-2.5 pr-4 text-brand-400">{{ $order['date'] ?? '—' }}</td>
                                        <td class="py-2.5 pr-4 text-white">{{ $order['status'] }}</td>
                                        <td class="py-2.5 pr-4 text-brand-400">{{ $order['payment_status'] }}</td>
                                        <td class="py-2.5 pr-4 text-right text-white font-semibold">{{ $order['total'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- ============== TAB: NOTES ============== --}}
        <div x-show="tab === 'notes'" x-cloak class="space-y-6">
            {{-- Add note form --}}
            <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-6">
                <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold mb-3">Add Note</div>
                <form wire:submit="addNote">
                    <textarea wire:model="noteBody" rows="3" placeholder="What happened? What's worth remembering about this customer?"
                        class="w-full bg-brand-800 border border-brand-700 text-white text-[0.875rem] rounded-lg px-3 py-2 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-300/15"></textarea>
                    @error('noteBody')
                        <p class="text-red-400 text-[0.8rem] mt-1.5">{{ $message }}</p>
                    @enderror
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-brand-400 text-[0.75rem]">Notes are visible to all staff with customer access.</span>
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-1.5 rounded-lg font-bold no-underline bg-brand-300 text-brand-900 hover:opacity-90 px-4 py-2 text-[0.85rem]">
                            <x-heroicon-o-plus class="w-3.5 h-3.5" stroke-width="2.5" />
                            Save Note
                        </button>
                    </div>
                </form>
            </div>

            {{-- List of notes --}}
            <div class="bg-brand-900 border border-brand-800/60 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-brand-300 text-[0.65rem] uppercase tracking-[0.1em] font-semibold">Notes</div>
                    <span class="text-brand-400 text-[0.75rem]">{{ $customer->customerNotes->count() }} total</span>
                </div>

                @php $allNotes = $customer->customerNotes->sortByDesc('created_at'); @endphp

                @if ($allNotes->isEmpty())
                    <div class="text-center py-10">
                        <x-heroicon-o-pencil-square class="w-10 h-10 text-brand-400/40 mx-auto mb-3" />
                        <div class="text-brand-200 text-[0.9rem] font-semibold">No notes yet</div>
                        <div class="text-brand-400 text-[0.8rem] mt-1">Add context about this customer so your team can pick up where you left off.</div>
                    </div>
                @else
                    <ul class="space-y-3">
                        @foreach ($allNotes as $note)
                            @php $authorName = $note->createdBy->name ?? 'Unknown'; @endphp
                            <li class="rounded-xl border border-brand-700/40 bg-brand-950 p-4" wire:key="note-{{ $note->id }}">
                                <div class="flex items-start justify-between gap-3 mb-2">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <div class="w-7 h-7 rounded-full bg-brand-300/15 border border-brand-300/25 flex items-center justify-center text-brand-300 font-bold text-[0.7rem]">
                                            {{ strtoupper(substr($authorName, 0, 2)) }}
                                        </div>
                                        <span class="text-white font-semibold text-[0.85rem]">{{ $authorName }}</span>
                                        <span class="text-brand-400 text-[0.75rem]">{{ $note->created_at?->diffForHumans() }}</span>
                                    </div>
                                    <button type="button" wire:click="deleteNote({{ $note->id }})"
                                        wire:confirm="Delete this note?"
                                        class="inline-flex items-center gap-1 text-brand-400 hover:text-red-400 text-[0.75rem] transition-colors cursor-pointer">
                                        <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                                <div class="text-brand-200 text-[0.9rem] leading-relaxed whitespace-pre-wrap">{{ $note->note }}</div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
