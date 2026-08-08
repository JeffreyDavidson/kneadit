@php
    $monthly = $this->monthlyData;
    $yearly = $this->yearlyData;

    // Tone maps progress percentage to a hex color (semantic: green = on track,
    // amber = building, red = falling behind).
    $toneColor = fn (float $pct): string => match (true) {
        $pct >= 75 => '#6b9e3a',
        $pct >= 30 => '#e8b04a',
        default => '#d4574a',
    };

    $toneLabel = fn (float $pct): string => match (true) {
        $pct >= 100 => 'Goal exceeded',
        $pct >= 75 => 'On track',
        $pct >= 30 => 'Building momentum',
        default => 'Falling behind',
    };
@endphp

<div>
    <x-admin.dashboard.preview-card heading="Goal Tracker" icon="heroicon-o-flag">
        @foreach ([
            ['key' => 'monthly', 'eyebrow' => 'Monthly Revenue Goal', 'data' => $monthly],
            ['key' => 'yearly', 'eyebrow' => 'Yearly Revenue Goal', 'data' => $yearly],
        ] as $goal)
            @php
                $d = $goal['data'];
                $pct = (float) $d['percentage'];
                $color = $toneColor($pct);
                $label = $toneLabel($pct);
                $remaining = max(0, $d['goal'] - $d['revenue']);
            @endphp

            <div @class(['mt-3' => ! $loop->first])>
                <div
                    style="
                        display: flex;
                        justify-content: space-between;
                        align-items: baseline;
                        gap: 8px;
                        flex-wrap: wrap;
                    "
                >
                    <div>
                        <span class="pw-stat-label">{{ $goal['eyebrow'] }}</span>
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--pw-card-text); margin-top: 2px">
                            @money($d['revenue'])
                            <span style="font-weight: 400; color: var(--pw-card-text-muted)">of</span>
                            ${{ number_format($d['goal'], 0) }}
                        </div>
                    </div>
                    <button
                        wire:click="openEditModal('{{ $goal['key'] }}')"
                        type="button"
                        style="
                            background: var(--pw-card-grad-start);
                            border: 1px solid var(--pw-card-border-subtle);
                            color: var(--pw-card-accent);
                            padding: 4px 10px;
                            border-radius: 6px;
                            font-size: 0.65rem;
                            font-weight: 600;
                            cursor: pointer;
                            display: inline-flex;
                            align-items: center;
                            gap: 4px;
                        "
                    >
                        <x-filament::icon icon="heroicon-o-pencil-square" class="h-3.5 w-3.5" />
                        Edit
                    </button>
                </div>

                <div style="margin-top: 8px">
                    <div class="pw-bar">
                        <div class="pw-bar-fill" style="width: {{ min(100, $pct) }}%; background: {{ $color }};"></div>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; font-size: 0.65rem; margin-top: 4px">
                    <span style="color: {{ $color }}; font-weight: 600;">{{ $label }}</span>
                    <span style="color: var(--pw-card-text-muted)">
                        @if ($pct >= 100)
                            Beat goal by
                            <strong style="color: {{ $color }};">@money($d['revenue'] - $d['goal'])</strong>
                        @else
                            <strong style="color: {{ $color }};">@money($remaining)</strong>
                            to go · {{ $d['percentage'] }}%
                        @endif
                    </span>
                </div>
            </div>
        @endforeach
    </x-admin.dashboard.preview-card>

    {{-- Edit modal --}}
    @if ($showEditModal)
        <x-admin.modal>
            <div class="text-brand-900 mb-4 text-base font-bold">Edit {{ ucfirst($editingType) }} Goal</div>
            <x-admin.eyebrow as="label" class="mb-1 block">Goal Amount ($)</x-admin.eyebrow>
            <x-admin.input type="number" wire:model="editingGoal" step="100" min="0" class="mb-4" />
            <div class="flex justify-end gap-2">
                <x-admin.btn variant="secondary" wire:click="closeEditModal" size="sm">Cancel</x-admin.btn>
                <button
                    wire:click="saveGoal"
                    type="button"
                    class="bg-brand-900 cursor-pointer rounded-lg border-0 px-4 py-2 text-[0.8rem] font-semibold text-white"
                >
                    Save
                </button>
            </div>
        </x-admin.modal>
    @endif
</div>
