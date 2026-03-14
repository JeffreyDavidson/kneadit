<x-filament-widgets::widget>
    <x-filament::section heading="Reorder Reminders" icon="heroicon-o-arrow-path">
        @php
            $count = $this->getLapsedCount();
            $customers = $this->getLapsedCustomers();
        @endphp

        <div style="background: #fdf8f2; border-radius: 8px; padding: 12px; text-align: center; margin-bottom: 16px;">
            <div style="font-size: 1.5rem; font-weight: 700; color: #3d2314;">{{ $count }}</div>
            <div style="font-size: 0.75rem; color: #6b4c3b;">Customers needing a nudge</div>
            <div style="font-size: 0.65rem; color: #8b6844;">Ordered 2+ times, inactive 30+ days</div>
        </div>

        @if(count($customers) > 0)
            @foreach($customers as $customer)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: #fdf8f2; border-radius: 6px; margin-bottom: 6px; font-size: 0.8rem;">
                    <div>
                        <div style="font-weight: 600; color: #3d2314;">{{ $customer['name'] }}</div>
                        <div style="font-size: 0.7rem; color: #8b6844;">{{ $customer['email'] }}</div>
                    </div>
                    <div style="font-size: 0.7rem; color: #6b4c3b; white-space: nowrap;">{{ $customer['last_order'] }}</div>
                </div>
            @endforeach
        @else
            <div style="color: #8b6844; font-style: italic; font-size: 0.8rem; text-align: center; padding: 12px;">
                All regular customers are active — great job!
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
