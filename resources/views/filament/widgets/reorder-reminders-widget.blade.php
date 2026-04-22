<x-filament-widgets::widget>
    <x-filament::section heading="Reorder Reminders" icon="heroicon-o-arrow-path">
        @php
            $count = $this->getLapsedCount();
            $customers = $this->getLapsedCustomers();
        @endphp

        <x-admin.stat-cell label="Customers needing a nudge" class="mb-4">
            {{ $count }}
            <div class="text-[0.65rem] text-brand-600 font-normal">Ordered 2+ times, inactive 30+ days</div>
        </x-admin.stat-cell>

        @if (count($customers) > 0)
            @foreach ($customers as $customer)
                <div class="flex justify-between items-center px-3 py-2 bg-brand-50 rounded-md mb-1.5 text-[0.8rem]">
                    <div>
                        <div class="font-semibold text-brand-900">{{ $customer['name'] }}</div>
                        <div class="text-[0.7rem] text-brand-600">{{ $customer['email'] }}</div>
                    </div>
                    <div class="text-[0.7rem] text-brand-700 whitespace-nowrap">{{ $customer['last_order'] }}</div>
                </div>
            @endforeach
        @else
            <div class="text-brand-600 italic text-[0.8rem] text-center p-3">
                All regular customers are active — great job!
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
