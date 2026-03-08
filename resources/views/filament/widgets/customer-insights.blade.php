<x-filament-widgets::widget>
    <x-filament::section heading="Customer Insights" icon="heroicon-o-user-group">
        @php
            $avg = $this->getAvgOrderValue();
        @endphp
        <div style="display: flex; flex-direction: column; gap: 16px;">
            {{-- New Customers --}}
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; background: linear-gradient(135deg, var(--brand-50), var(--brand-100)); border-radius: 12px;">
                <div>
                    <div style="font-size: 0.78rem; color: var(--brand-700); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">New This Week</div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--brand-900);">{{ $this->getNewCustomersThisWeek() }}</div>
                </div>
                <div style="background: var(--brand-700); color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <x-heroicon-s-user-plus style="width: 20px; height: 20px;" />
                </div>
            </div>

            {{-- Repeat Rate --}}
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; background: linear-gradient(135deg, var(--accent-gold-light), var(--brand-200)); border-radius: 12px;">
                <div>
                    <div style="font-size: 0.78rem; color: var(--status-warning-dark); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Repeat Rate</div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--brand-900);">{{ $this->getRepeatCustomerRate() }}%</div>
                </div>
                <div style="background: var(--accent-gold); color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <x-heroicon-s-arrow-path style="width: 20px; height: 20px;" />
                </div>
            </div>

            {{-- AOV --}}
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; background: linear-gradient(135deg, var(--brand-150), var(--brand-100)); border-radius: 12px;">
                <div>
                    <div style="font-size: 0.78rem; color: var(--brand-600); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Avg Order Value</div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--brand-900);">
                        ${{ number_format($avg['value'], 2) }}
                        <span style="font-size: 0.85rem; color: {{ $avg['trend'] === 'up' ? 'var(--status-success)' : 'var(--status-danger)' }};">
                            {{ $avg['trend'] === 'up' ? '↑' : '↓' }}
                        </span>
                    </div>
                </div>
                <div style="background: var(--brand-600); color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <x-heroicon-s-banknotes style="width: 20px; height: 20px;" />
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
