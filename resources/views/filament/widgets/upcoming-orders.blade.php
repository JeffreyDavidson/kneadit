<x-filament-widgets::widget>
    <x-filament::section heading="Upcoming Orders" icon="heroicon-o-calendar-days">
        @php $groups = $this->getUpcomingOrders(); @endphp

        @forelse ($groups as $date => $group)
            <div style="margin-bottom: 16px;">
                <div style="font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #8B5E3C; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 2px solid #F5E6D3;">
                    {{ $group['label'] }} ({{ count($group['orders']) }})
                </div>
                @foreach ($group['orders'] as $order)
                    <a href="{{ route('filament.admin.resources.orders.edit', $order['id']) }}"
                       style="display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; border-radius: 8px; text-decoration: none; color: inherit; margin-bottom: 4px; transition: background 0.15s;"
                       onmouseover="this.style.background='#F5E6D3'" onmouseout="this.style.background='transparent'">
                        <div>
                            <span style="font-weight: 600; font-size: 0.85rem; color: #374151;">{{ $order['customer'] }}</span>
                            <span style="font-size: 0.75rem; color: #9CA3AF; margin-left: 6px;">{{ $order['items'] }} items</span>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 0.8rem; color: #6B7280;">{{ $order['time'] }}</span>
                            <span style="font-size: 0.8rem; font-weight: 600; color: #8B5E3C; margin-left: 8px;">${{ $order['total'] }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @empty
            <div style="text-align: center; padding: 24px; color: #9CA3AF;">
                <div style="font-size: 2rem; margin-bottom: 8px;">📋</div>
                <p style="margin: 0;">No upcoming orders in the next 3 days</p>
            </div>
        @endforelse
    </x-filament::section>
</x-filament-widgets::widget>
