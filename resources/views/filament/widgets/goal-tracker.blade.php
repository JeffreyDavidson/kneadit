<x-filament-widgets::widget>
    <style>
        .goal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        @media (max-width: 768px) { .goal-grid { grid-template-columns: 1fr; } }
        .goal-card { background: #fff; border: 1px solid var(--brand-200); border-radius: 12px; overflow: hidden; }
        [data-admin-gradient-header] button { background: rgba(255,255,255,0.2); border: none; color: #fff; border-radius: 6px; padding: 0.25rem 0.75rem; cursor: pointer; font-size: 0.75rem; font-weight: 600; }
        .goal-card-body { padding: 1.25rem; }
        .goal-label { font-size: 0.8rem; color: var(--brand-600); font-weight: 600; margin-bottom: 0.5rem; }
        .goal-bar { background: var(--brand-100); border-radius: 999px; height: 24px; overflow: hidden; margin-bottom: 0.75rem; }
        .goal-bar-fill { background: linear-gradient(90deg, var(--brand-700), var(--brand-900)); height: 100%; border-radius: 999px; transition: width 0.5s ease; display: flex; align-items: center; justify-content: flex-end; padding-right: 8px; }
        .goal-bar-pct { color: #fff; font-size: 0.7rem; font-weight: 700; }
        .goal-footer { display: flex; justify-content: space-between; align-items: baseline; }
        .goal-revenue { font-family: 'Playfair Display', serif; font-size: 1.4rem; font-weight: 700; color: var(--brand-900); }
        .goal-of { font-size: 0.8rem; color: var(--brand-600); }
        .goal-pct { font-size: 1.1rem; font-weight: 700; }
    </style>

    @php $monthly = $this->monthlyData; $yearly = $this->yearlyData; @endphp

    <div class="goal-grid">
        {{-- Monthly --}}
        <div class="goal-card">
            <div data-admin-gradient-header>
                <span data-header-title>Monthly Goal</span>
                <button wire:click="openEditModal('monthly')" type="button">Edit</button>
            </div>
            <div class="goal-card-body">
                <div class="goal-label">{{ $monthly['label'] }}</div>
                <div class="goal-bar">
                    <div class="goal-bar-fill" style="width: {{ $monthly['percentage'] }}%; min-width: {{ $monthly['percentage'] > 5 ? 0 : 40 }}px;">
                        @if($monthly['percentage'] > 10)<span class="goal-bar-pct">{{ $monthly['percentage'] }}%</span>@endif
                    </div>
                </div>
                <div class="goal-footer">
                    <div>
                        <span class="goal-revenue">${{ number_format($monthly['revenue'], 2) }}</span>
                        <span class="goal-of"> / ${{ number_format($monthly['goal'], 0) }}</span>
                    </div>
                    <span class="goal-pct" style="color: {{ $monthly['percentage'] >= 100 ? 'var(--status-success)' : 'var(--brand-900)' }};">{{ $monthly['percentage'] }}%</span>
                </div>
            </div>
        </div>

        {{-- Yearly --}}
        <div class="goal-card">
            <div data-admin-gradient-header>
                <span data-header-title>Yearly Goal</span>
                <button wire:click="openEditModal('yearly')" type="button">Edit</button>
            </div>
            <div class="goal-card-body">
                <div class="goal-label">{{ $yearly['label'] }}</div>
                <div class="goal-bar">
                    <div class="goal-bar-fill" style="width: {{ $yearly['percentage'] }}%; min-width: {{ $yearly['percentage'] > 5 ? 0 : 40 }}px;">
                        @if($yearly['percentage'] > 10)<span class="goal-bar-pct">{{ $yearly['percentage'] }}%</span>@endif
                    </div>
                </div>
                <div class="goal-footer">
                    <div>
                        <span class="goal-revenue">${{ number_format($yearly['revenue'], 2) }}</span>
                        <span class="goal-of"> / ${{ number_format($yearly['goal'], 0) }}</span>
                    </div>
                    <span class="goal-pct" style="color: {{ $yearly['percentage'] >= 100 ? 'var(--status-success)' : 'var(--brand-900)' }};">{{ $yearly['percentage'] }}%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit modal --}}
    @if ($showEditModal)
        <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 50; display: flex; align-items: center; justify-content: center;" wire:click.self="closeEditModal">
            <div style="background: #fff; border-radius: 12px; border: 1px solid var(--brand-200); padding: 1.5rem; width: 360px; max-width: 90vw;">
                <div style="font-weight: 700; color: var(--brand-900); font-size: 1rem; margin-bottom: 1rem;">Edit {{ ucfirst($editingType) }} Goal</div>
                <label style="font-size: 0.8rem; color: var(--brand-700); font-weight: 600; display: block; margin-bottom: 0.25rem;">Goal Amount ($)</label>
                <input type="number" wire:model="editingGoal" step="100" min="0" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--brand-200); border-radius: 8px; font-size: 0.9rem; margin-bottom: 1rem; outline: none; color: var(--brand-900);" onfocus="this.style.borderColor='var(--brand-300)';this.style.boxShadow='0 0 0 3px rgba(212,165,116,0.15)'" onblur="this.style.borderColor='var(--brand-200)';this.style.boxShadow='none'">
                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                    <button wire:click="closeEditModal" type="button" style="padding: 0.5rem 1rem; border: 1px solid var(--brand-200); background: var(--brand-50); border-radius: 8px; cursor: pointer; font-size: 0.8rem; font-weight: 600; color: var(--brand-700);">Cancel</button>
                    <button wire:click="saveGoal" type="button" style="padding: 0.5rem 1rem; border: none; background: var(--brand-900); color: #fff; border-radius: 8px; cursor: pointer; font-size: 0.8rem; font-weight: 600;">Save</button>
                </div>
            </div>
        </div>
    @endif
</x-filament-widgets::widget>
