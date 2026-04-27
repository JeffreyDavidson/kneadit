<x-filament-widgets::widget>
    @php
        $monthly = $this->monthlyData;
        $yearly = $this->yearlyData;

        $tone = function (float $percentage): array {
            return match (true) {
                $percentage >= 100 => ['accent' => 'emerald', 'label' => 'Goal exceeded'],
                $percentage >= 75 => ['accent' => 'emerald', 'label' => 'On track'],
                $percentage >= 30 => ['accent' => 'amber', 'label' => 'Building momentum'],
                default => ['accent' => 'rose', 'label' => 'Falling behind'],
            };
        };
    @endphp

    <div class="flex flex-col gap-3">
        @foreach ([
            ['key' => 'monthly', 'eyebrow' => 'Monthly Revenue Goal', 'data' => $monthly],
            ['key' => 'yearly', 'eyebrow' => 'Yearly Revenue Goal', 'data' => $yearly],
        ] as $goal)
            @php
                $d = $goal['data'];
                $t = $tone((float) $d['percentage']);
                $remaining = max(0, $d['goal'] - $d['revenue']);
            @endphp

            <div class="rounded-xl border border-brand-100 bg-white px-5 py-4 flex items-center gap-5 flex-wrap">
                <div class="w-11 h-11 rounded-xl bg-{{ $t['accent'] }}-50 border border-{{ $t['accent'] }}-200 flex items-center justify-center shrink-0">
                    <x-heroicon-o-flag class="w-5 h-5 text-{{ $t['accent'] }}-600" />
                </div>

                <div class="shrink-0 min-w-55">
                    <div class="text-[0.7rem] uppercase tracking-wider font-semibold text-brand-600">{{ $goal['eyebrow'] }}</div>
                    <div class="text-brand-900 font-bold text-[1.05rem] leading-tight">
                        @money($d['revenue'])
                        <span class="text-brand-500 font-normal">of</span>
                        ${{ number_format($d['goal'], 0) }}
                    </div>
                    <div class="text-[0.8rem] text-brand-500 mt-1">
                        {{ $d['label'] }} ·
                        @if ($d['percentage'] >= 100)
                            Beat goal by <strong class="text-{{ $t['accent'] }}-700">@money($d['revenue'] - $d['goal'])</strong>
                        @else
                            <strong class="text-{{ $t['accent'] }}-700">@money($remaining)</strong> to go
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-3 flex-1 min-w-65">
                    <div class="bg-brand-100 rounded-full h-2 overflow-hidden flex-1">
                        <div class="h-full rounded-full bg-linear-to-r from-{{ $t['accent'] }}-400 to-{{ $t['accent'] }}-600 transition-all"
                             style="width: {{ $d['percentage'] }}%;"></div>
                    </div>
                    <span class="text-{{ $t['accent'] }}-700 text-[0.95rem] font-bold tabular-nums shrink-0">{{ $d['percentage'] }}%</span>
                </div>

                <button wire:click="openEditModal('{{ $goal['key'] }}')" type="button"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[0.75rem] font-semibold bg-brand-50 text-brand-700 border border-brand-100 hover:bg-brand-100 transition-colors shrink-0">
                    <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                    Edit
                </button>
            </div>
        @endforeach
    </div>

    {{-- Edit modal --}}
    @if ($showEditModal)
        <x-admin.modal>
            <div class="font-bold text-brand-900 text-base mb-4">Edit {{ ucfirst($editingType) }} Goal</div>
            <x-admin.eyebrow as="label" class="block mb-1">Goal Amount ($)</x-admin.eyebrow>
            <x-admin.input type="number" wire:model="editingGoal" step="100" min="0" class="mb-4" />
            <div class="flex gap-2 justify-end">
                <x-admin.btn variant="secondary" wire:click="closeEditModal" size="sm">Cancel</x-admin.btn>
                <button wire:click="saveGoal" type="button" class="px-4 py-2 border-0 bg-brand-900 text-white rounded-lg cursor-pointer text-[0.8rem] font-semibold">Save</button>
            </div>
        </x-admin.modal>
    @endif
</x-filament-widgets::widget>
