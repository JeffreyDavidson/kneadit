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
    <div class="bg-brand-900 border-brand-800/60 mb-6 flex flex-col gap-5 rounded-xl border p-6 md:flex-row md:items-center">
        <div class="flex min-w-0 flex-1 items-center gap-4">
            <div class="bg-brand-300/15 border-brand-300/25 text-brand-300 flex h-14 w-14 shrink-0 items-center justify-center rounded-xl border text-[1.15rem] font-bold">
                {{ $initials($customer->name ?: $detail['email'] ?? '?') }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="mb-1 flex items-center gap-2">
                    <h2 class="truncate text-[1.35rem] leading-tight font-bold text-white">{{ $customer->name }}</h2>
                </div>
                @if ($detail['email'])
                    <a
                        href="mailto:{{ $detail['email'] }}"
                        class="text-brand-400 hover:text-brand-300 inline-flex items-center gap-1.5 text-[0.85rem] transition-colors"
                    >
                        {{ $detail['email'] }}
                    </a>
                @endif
            </div>
        </div>

        {{-- Status pills --}}
        <div class="flex flex-wrap items-center gap-2">
            @if ($isAtRisk)
                <span class="inline-flex items-center gap-1.5 rounded-full border border-red-500/25 bg-red-500/15 px-2.5 py-1 text-[0.7rem] font-bold tracking-[0.08em] text-red-400 uppercase">
                    <x-heroicon-o-exclamation-triangle class="h-3 w-3" />
                    At Risk
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/25 bg-emerald-500/15 px-2.5 py-1 text-[0.7rem] font-bold tracking-[0.08em] text-emerald-400 uppercase">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Active
                </span>
            @endif

            @if (($stats['total_points'] ?? 0) > 0)
                <span class="bg-brand-300/10 border-brand-300/25 text-brand-300 inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[0.7rem] font-bold tracking-[0.08em] uppercase">
                    <x-heroicon-o-sparkles class="h-3 w-3" />
                    {{ number_format($stats['total_points']) }} pts
                </span>
            @endif

            @if ($createdAt)
                <span class="bg-brand-800 border-brand-300/15 text-brand-200 inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[0.7rem] font-semibold tracking-[0.08em] uppercase">
                    <x-heroicon-o-calendar class="h-3 w-3" />
                    Since {{ $createdAt->format('M Y') }}
                </span>
            @endif
        </div>
    </div>

    {{-- ============== TABS ============== --}}
    <div x-data="{ tab: 'overview' }" class="space-y-6">
        <div class="border-brand-300/12 flex items-center gap-1 overflow-x-auto border-b">
            @php
                $tabs = [
                    'overview' => ['label' => 'Overview', 'icon' => 'chart-bar-square'],
                    'orders' => ['label' => 'Orders', 'icon' => 'receipt-percent', 'count' => count($orders)],
                    'loyalty' => ['label' => 'Loyalty', 'icon' => 'sparkles', 'count' => $customer->loyaltyPoints->count()],
                    'notes' => ['label' => 'Notes', 'icon' => 'pencil-square', 'count' => $customer->customerNotes->count()],
                ];
            @endphp
            @foreach ($tabs as $key => $t)
                <button
                    type="button"
                    @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}'
                        ? 'text-white border-brand-300'
                        : 'text-brand-400 border-transparent hover:text-brand-200'"
                    class="-mb-px inline-flex cursor-pointer items-center gap-2 border-b-2 px-4 py-2.5 text-[0.85rem] font-semibold whitespace-nowrap transition-colors"
                >
                    @switch ($t['icon'])
                        @case ('chart-bar-square')
                            <x-heroicon-o-chart-bar-square class="h-4 w-4" />
                            @break
                        @case ('receipt-percent')
                            <x-heroicon-o-receipt-percent class="h-4 w-4" />
                            @break
                        @case ('sparkles')
                            <x-heroicon-o-sparkles class="h-4 w-4" />
                            @break
                        @case ('pencil-square')
                            <x-heroicon-o-pencil-square class="h-4 w-4" />
                            @break
                    @endswitch
                    {{ $t['label'] }}
                    @isset($t['count'])
                        <span
                            :class="tab === '{{ $key }}' ? 'bg-brand-300/15 text-brand-300' : 'bg-brand-800 text-brand-400'"
                            class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full px-1.5 text-[0.7rem] font-bold transition-colors"
                        >
                            {{ $t['count'] }}
                        </span>
                    @endisset
                </button>
            @endforeach
        </div>

        {{-- ============== TAB: OVERVIEW ============== --}}
        <div x-show="tab === 'overview'" x-cloak class="space-y-6">
            {{-- Stats --}}
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
                <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-4">
                    <div class="text-brand-300 mb-1 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">
                        Lifetime Value
                    </div>
                    <div class="text-[1.5rem] leading-none font-bold text-white">@money($stats['total_spent'])</div>
                </div>
                <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-4">
                    <div class="text-brand-300 mb-1 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">
                        Orders
                    </div>
                    <div class="text-[1.5rem] leading-none font-bold text-white">
                        {{ number_format($stats['total_orders']) }}
                    </div>
                </div>
                <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-4">
                    <div class="text-brand-300 mb-1 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">
                        Avg Order
                    </div>
                    <div class="text-[1.5rem] leading-none font-bold text-white">@money($stats['avg_order_value'])</div>
                </div>
                <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-4">
                    <div class="text-brand-300 mb-1 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">
                        Points
                    </div>
                    <div class="text-[1.5rem] leading-none font-bold text-white">
                        {{ number_format($stats['total_points'] ?? 0) }}
                    </div>
                </div>
                <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-4">
                    <div class="text-brand-300 mb-1 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">
                        Lifetime Points
                    </div>
                    <div class="text-[1.5rem] leading-none font-bold text-white">
                        {{ number_format($stats['lifetime_points'] ?? 0) }}
                    </div>
                </div>
                <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-4">
                    <div class="text-brand-300 mb-1 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">
                        Last Order
                    </div>
                    <div class="mt-0.5 text-[0.95rem] leading-tight font-semibold text-white">
                        {{ ($stats['last_order_at'] ?? null) ? Carbon::parse($stats['last_order_at'])->diffForHumans() : 'Never' }}
                    </div>
                </div>
            </div>

            {{-- Details --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-6">
                    <div class="text-brand-300 mb-4 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">
                        Contact
                    </div>
                    <dl class="divide-brand-700/40 divide-y">
                        @foreach ($contactRows as $r)
                            <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                                <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">{{ $r['label'] }}</dt>
                                <dd class="text-white text-[0.85rem] font-semibold text-right truncate {{ $r['mono'] ? 'font-mono text-brand-200' : '' }}">
                                    {{ $r['value'] }}
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-6">
                    <div class="text-brand-300 mb-4 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">
                        Account
                    </div>
                    <dl class="divide-brand-700/40 divide-y">
                        @foreach ($accountRows as $r)
                            <div class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0">
                                <dt class="text-brand-400 shrink-0 pt-0.5 text-[0.8rem]">{{ $r['label'] }}</dt>
                                <dd class="text-white text-[0.85rem] font-semibold text-right truncate {{ $r['mono'] ? 'font-mono text-brand-200' : '' }}">
                                    {{ $r['value'] }}
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </div>
        </div>

        {{-- ============== TAB: ORDERS ============== --}}
        <div x-show="tab === 'orders'" x-cloak>
            <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div class="text-brand-300 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">
                        Order History
                    </div>
                    <span class="text-brand-400 text-[0.75rem]">{{ count($orders) }} total</span>
                </div>

                @if ($orders === [])
                    <div class="py-12 text-center">
                        <x-heroicon-o-receipt-percent class="text-brand-400/40 mx-auto mb-3 h-10 w-10" />
                        <div class="text-brand-200 text-[0.9rem] font-semibold">No orders yet</div>
                        <div class="text-brand-400 mt-1 text-[0.8rem]">
                            Orders placed by this customer will appear here.
                        </div>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-[0.85rem]">
                            <thead class="border-brand-700/40 text-brand-400 border-b text-[0.7rem] tracking-[0.05em] uppercase">
                                <tr>
                                    <th class="py-2 pr-4 font-semibold">Order</th>
                                    <th class="py-2 pr-4 font-semibold">Date</th>
                                    <th class="py-2 pr-4 font-semibold">Status</th>
                                    <th class="py-2 pr-4 font-semibold">Payment</th>
                                    <th class="py-2 pr-4 text-right font-semibold">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-brand-700/40 divide-y">
                                @foreach ($orders as $order)
                                    <tr>
                                        <td class="text-brand-200 py-2.5 pr-4 font-mono">
                                            {{ $order['order_number'] }}
                                        </td>
                                        <td class="text-brand-400 py-2.5 pr-4">{{ $order['date'] ?? '—' }}</td>
                                        <td class="py-2.5 pr-4 text-white">{{ $order['status'] }}</td>
                                        <td class="text-brand-400 py-2.5 pr-4">{{ $order['payment_status'] }}</td>
                                        <td class="py-2.5 pr-4 text-right font-semibold text-white">
                                            {{ $order['total'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- ============== TAB: LOYALTY ============== --}}
        <div x-show="tab === 'loyalty'" x-cloak class="space-y-6">
            {{-- Balance summary --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-6">
                    <div class="text-brand-300 mb-1 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">
                        Current Balance
                    </div>
                    <div class="text-[1.75rem] leading-none font-bold text-white">
                        {{ number_format($stats['total_points'] ?? 0) }}
                    </div>
                    <div class="text-brand-400 mt-1 text-[0.75rem]">Points available to redeem</div>
                </div>
                <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-6">
                    <div class="text-brand-300 mb-1 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">
                        Lifetime Earned
                    </div>
                    <div class="text-[1.75rem] leading-none font-bold text-white">
                        {{ number_format($stats['lifetime_points'] ?? 0) }}
                    </div>
                    <div class="text-brand-400 mt-1 text-[0.75rem]">Total points credited over time</div>
                </div>
            </div>

            {{-- Ledger --}}
            <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div class="text-brand-300 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">Ledger</div>
                    <span class="text-brand-400 text-[0.75rem]">{{ $customer->loyaltyPoints->count() }} {{ Str::plural('entry', $customer->loyaltyPoints->count()) }}</span>
                </div>

                @php $entries = $customer->loyaltyPoints->sortByDesc('created_at'); @endphp

                @if ($entries->isEmpty())
                    <div class="py-10 text-center">
                        <x-heroicon-o-sparkles class="text-brand-400/40 mx-auto mb-3 h-10 w-10" />
                        <div class="text-brand-200 text-[0.9rem] font-semibold">No loyalty activity yet</div>
                        <div class="text-brand-400 mt-1 text-[0.8rem]">
                            Use Adjust Points or Manual Redemption above to record the first entry.
                        </div>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-[0.85rem]">
                            <thead class="border-brand-700/40 text-brand-400 border-b text-[0.7rem] tracking-[0.05em] uppercase">
                                <tr>
                                    <th class="py-2 pr-4 font-semibold">Date</th>
                                    <th class="py-2 pr-4 font-semibold">Type</th>
                                    <th class="py-2 pr-4 font-semibold">Reason</th>
                                    <th class="py-2 text-right font-semibold">Points</th>
                                </tr>
                            </thead>
                            <tbody class="divide-brand-700/40 divide-y">
                                @foreach ($entries as $entry)
                                    @php
                                        $isRedeemed = $entry->type === \App\Enums\Engagement\LoyaltyPointType::Redeemed;
                                        $signed = $isRedeemed ? -abs($entry->points) : (int) $entry->points;
                                        $typePill = match ($entry->type->value) {
                                            'earned' => 'bg-emerald-500/15 border-emerald-500/25 text-emerald-400',
                                            'redeemed' => 'bg-amber-500/15 border-amber-500/25 text-amber-400',
                                            'adjusted' => 'bg-sky-500/15 border-sky-500/25 text-sky-400',
                                            default => 'bg-brand-800 border-brand-700 text-brand-200',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="text-brand-400 py-3 pr-4 whitespace-nowrap">
                                            {{ $entry->created_at?->format('M j, Y') ?? '—' }}
                                        </td>
                                        <td class="py-3 pr-4">
                                            <span class="inline-flex items-center px-2 py-0.5 border rounded-full text-[0.65rem] font-bold uppercase tracking-[0.06em] {{ $typePill }}">
                                                {{ $entry->type->getLabel() }}
                                            </span>
                                        </td>
                                        <td class="text-brand-200 py-3 pr-4">{{ $entry->description ?: '—' }}</td>
                                        <td class="py-3 text-right font-semibold tabular-nums whitespace-nowrap {{ $signed >= 0 ? 'text-emerald-400' : 'text-amber-400' }}">
                                            {{ $signed >= 0 ? '+' : '' }}{{ number_format($signed) }}
                                        </td>
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
            <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-6">
                <div class="text-brand-300 mb-3 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">Add Note</div>
                <form wire:submit="addNote">
                    <textarea
                        wire:model="noteBody"
                        rows="3"
                        placeholder="What happened? What's worth remembering about this customer?"
                        class="bg-brand-800 border-brand-700 focus:border-brand-300 focus:ring-brand-300/15 w-full rounded-lg border px-3 py-2 text-[0.875rem] text-white focus:ring-2 focus:outline-none"
                    ></textarea>
                    @error('noteBody')
                        <p class="mt-1.5 text-[0.8rem] text-red-400">{{ $message }}</p>
                    @enderror
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-brand-400 text-[0.75rem]">Notes are visible to all staff with customer access.</span>
                        <button
                            type="submit"
                            class="bg-brand-300 text-brand-900 inline-flex items-center justify-center gap-1.5 rounded-lg px-4 py-2 text-[0.85rem] font-bold no-underline hover:opacity-90"
                        >
                            <x-heroicon-o-plus class="h-3.5 w-3.5" stroke-width="2.5" />
                            Save Note
                        </button>
                    </div>
                </form>
            </div>

            {{-- List of notes --}}
            <div class="bg-brand-900 border-brand-800/60 rounded-xl border p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div class="text-brand-300 text-[0.65rem] font-semibold tracking-[0.1em] uppercase">Notes</div>
                    <span class="text-brand-400 text-[0.75rem]">{{ $customer->customerNotes->count() }} total</span>
                </div>

                @php $allNotes = $customer->customerNotes->sortByDesc('created_at'); @endphp

                @if ($allNotes->isEmpty())
                    <div class="py-10 text-center">
                        <x-heroicon-o-pencil-square class="text-brand-400/40 mx-auto mb-3 h-10 w-10" />
                        <div class="text-brand-200 text-[0.9rem] font-semibold">No notes yet</div>
                        <div class="text-brand-400 mt-1 text-[0.8rem]">
                            Add context about this customer so your team can pick up where you left off.
                        </div>
                    </div>
                @else
                    <ul class="space-y-3">
                        @foreach ($allNotes as $note)
                            @php $authorName = $note->createdBy->name ?? 'Unknown'; @endphp
                            <li
                                class="border-brand-700/40 bg-brand-950 rounded-xl border p-4"
                                wire:key="note-{{ $note->id }}"
                            >
                                <div class="mb-2 flex items-start justify-between gap-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <div class="bg-brand-300/15 border-brand-300/25 text-brand-300 flex h-7 w-7 items-center justify-center rounded-full border text-[0.7rem] font-bold">
                                            {{ strtoupper(substr($authorName, 0, 2)) }}
                                        </div>
                                        <span class="text-[0.85rem] font-semibold text-white">{{ $authorName }}</span>
                                        <span class="text-brand-400 text-[0.75rem]">{{ $note->created_at?->diffForHumans() }}</span>
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="deleteNote({{ $note->id }})"
                                        wire:confirm="Delete this note?"
                                        class="text-brand-400 inline-flex cursor-pointer items-center gap-1 text-[0.75rem] transition-colors hover:text-red-400"
                                    >
                                        <x-heroicon-o-trash class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                                <div class="text-brand-200 text-[0.9rem] leading-relaxed whitespace-pre-wrap">
                                    {{ $note->note }}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
