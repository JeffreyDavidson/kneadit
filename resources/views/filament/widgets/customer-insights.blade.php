<x-filament-widgets::widget>
    <x-filament::section heading="Customer Insights" icon="heroicon-o-user-group">
        @php
            $avg = $this->getAvgOrderValue();
        @endphp
        <div style="display: flex; flex-direction: column; gap: 16px;">
            {{-- New Customers --}}
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; background: linear-gradient(135deg, #EEF2FF, #E0E7FF); border-radius: 12px;">
                <div>
                    <div style="font-size: 0.78rem; color: #6366F1; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">New This Week</div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: #312E81;">{{ $this->getNewCustomersThisWeek() }}</div>
                </div>
                <div style="background: #6366F1; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <x-heroicon-s-user-plus style="width: 20px; height: 20px;" />
                </div>
            </div>

            {{-- Repeat Rate --}}
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; background: linear-gradient(135deg, #FEF3C7, #FDE68A); border-radius: 12px;">
                <div>
                    <div style="font-size: 0.78rem; color: #92400E; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Repeat Rate</div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: #78350F;">{{ $this->getRepeatCustomerRate() }}%</div>
                </div>
                <div style="background: #F59E0B; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <x-heroicon-s-arrow-path style="width: 20px; height: 20px;" />
                </div>
            </div>

            {{-- AOV --}}
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; background: linear-gradient(135deg, #D1FAE5, #A7F3D0); border-radius: 12px;">
                <div>
                    <div style="font-size: 0.78rem; color: #065F46; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Avg Order Value</div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: #064E3B;">
                        ${{ number_format($avg['value'], 2) }}
                        <span style="font-size: 0.85rem; color: {{ $avg['trend'] === 'up' ? '#059669' : '#DC2626' }};">
                            {{ $avg['trend'] === 'up' ? '↑' : '↓' }}
                        </span>
                    </div>
                </div>
                <div style="background: #10B981; color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <x-heroicon-s-banknotes style="width: 20px; height: 20px;" />
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
