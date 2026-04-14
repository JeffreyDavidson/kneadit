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
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 20px; height: 20px;"><path d="M5.25 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM2.25 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63l-.001-.122ZM18.75 7.5a.75.75 0 0 1 .75.75v1.5h1.5a.75.75 0 0 1 0 1.5h-1.5v1.5a.75.75 0 0 1-1.5 0v-1.5h-1.5a.75.75 0 0 1 0-1.5h1.5v-1.5a.75.75 0 0 1 .75-.75Z" /></svg>
                </div>
            </div>

            {{-- Repeat Rate --}}
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; background: linear-gradient(135deg, var(--accent-gold-light), var(--brand-200)); border-radius: 12px;">
                <div>
                    <div style="font-size: 0.78rem; color: var(--status-warning-dark); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Repeat Rate</div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--brand-900);">{{ $this->getRepeatCustomerRate() }}%</div>
                </div>
                <div style="background: var(--accent-gold); color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 20px; height: 20px;"><path fill-rule="evenodd" d="M4.755 10.059a7.5 7.5 0 0 1 12.548-3.364l1.903 1.903H14.25a.75.75 0 0 0 0 1.5h6.75a.75.75 0 0 0 .75-.75V2.598a.75.75 0 0 0-1.5 0v4.956l-1.904-1.903a9 9 0 0 0-15.059 4.038.75.75 0 0 0 1.468.318Zm-.005 3.889a.75.75 0 0 0-1.468.318 9 9 0 0 0 15.059 4.038l1.904 1.903a.75.75 0 0 0 1.5 0v-6.75a.75.75 0 0 0-.75-.75h-6.75a.75.75 0 0 0 0 1.5h4.956l-1.903 1.903a7.5 7.5 0 0 1-12.548 3.364v-.006Z" clip-rule="evenodd" /></svg>
                </div>
            </div>

            {{-- AOV --}}
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; background: linear-gradient(135deg, var(--brand-150), var(--brand-100)); border-radius: 12px;">
                <div>
                    <div style="font-size: 0.78rem; color: var(--brand-600); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Avg Order Value</div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--brand-900);">
                        @money($avg['value'])
                        <span style="font-size: 0.85rem; color: {{ $avg['trend'] === 'up' ? 'var(--status-success)' : 'var(--status-danger)' }};">
                            {{ $avg['trend'] === 'up' ? '↑' : '↓' }}
                        </span>
                    </div>
                </div>
                <div style="background: var(--brand-600); color: white; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 20px; height: 20px;"><path d="M12 7.5a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z" /><path fill-rule="evenodd" d="M1.5 4.875C1.5 3.839 2.34 3 3.375 3h17.25c1.035 0 1.875.84 1.875 1.875v9.75c0 1.036-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 0 1 1.5 14.625v-9.75ZM8.25 9.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM18.75 9a.75.75 0 0 0-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 0 0 .75-.75V9.75a.75.75 0 0 0-.75-.75h-.008ZM4.5 9.75A.75.75 0 0 1 5.25 9h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H5.25a.75.75 0 0 1-.75-.75V9.75Z" clip-rule="evenodd" /><path d="M2.25 18a.75.75 0 0 0 0 1.5c5.4 0 10.63.722 15.6 2.075 1.19.324 2.4-.558 2.4-1.82V18.75a.75.75 0 0 0-.75-.75H2.25Z" /></svg>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
