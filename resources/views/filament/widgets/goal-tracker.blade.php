<x-filament-widgets::widget>
    @php $monthly = $this->monthlyData; $yearly = $this->yearlyData; @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach ([['key' => 'monthly', 'title' => 'Monthly Goal', 'data' => $monthly], ['key' => 'yearly', 'title' => 'Yearly Goal', 'data' => $yearly]] as $goal)
            @php $d = $goal['data']; @endphp
            <x-admin.card>
                <x-slot:title>
                    <div class="flex items-center justify-between gap-3">
                        <span data-header-title>{{ $goal['title'] }}</span>
                        <button wire:click="openEditModal('{{ $goal['key'] }}')" type="button" class="bg-white/20 border-0 text-white rounded-md px-3 py-1 cursor-pointer text-xs font-semibold">Edit</button>
                    </div>
                </x-slot:title>

                <div class="text-[0.8rem] text-brand-600 font-semibold mb-2">{{ $d['label'] }}</div>
                <div class="bg-brand-100 rounded-full h-6 overflow-hidden mb-3">
                    <div class="h-full rounded-full flex items-center justify-end pr-2 transition-all duration-500 bg-gradient-to-r from-brand-700 to-brand-900"
                         style="width: {{ $d['percentage'] }}%; min-width: {{ $d['percentage'] > 5 ? 0 : 40 }}px;">
                        @if ($d['percentage'] > 10)<span class="text-white text-[0.7rem] font-bold">{{ $d['percentage'] }}%</span>@endif
                    </div>
                </div>
                <div class="flex justify-between items-baseline">
                    <div>
                        <span class="font-display text-[1.4rem] font-bold text-brand-900">@money($d['revenue'])</span>
                        <span class="text-[0.8rem] text-brand-600"> / ${{ number_format($d['goal'], 0) }}</span>
                    </div>
                    <span class="text-[1.1rem] font-bold {{ $d['percentage'] >= 100 ? 'text-emerald-600' : 'text-brand-900' }}">{{ $d['percentage'] }}%</span>
                </div>
            </x-admin.card>
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
